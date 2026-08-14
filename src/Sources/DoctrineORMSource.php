<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Exceptions\FilterInvalidException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Filters\Interfaces\FilterRange;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\DataTable\Source;
use JuniWalk\DataTable\Tools\FormatValue;

/**
 * @phpstan-import-type Items from Source
 */
class DoctrineORMSource extends AbstractSource
{
	protected readonly QueryBuilder $copy;
	protected int $placeholder;

	/** @var array<string, mixed> */
	protected array $hints = [];

	public function __construct(
		protected QueryBuilder $queryBuilder,
		protected string $primaryKey = 'id',
	) {
		$this->placeholder = sizeof($queryBuilder->getParameters());
		$this->copy = clone $queryBuilder;
	}


	public static function isModel(mixed $model): bool
	{
		return $model instanceof QueryBuilder;
	}


	public function clear(): void
	{
		$this->placeholder = sizeof($this->copy->getParameters());
		$this->queryBuilder = clone $this->copy;
		$this->countOnPage = null;
		$this->count = null;
	}


	public function getCount(): ?int
	{
		if ($this->isIndeterminate || isset($this->count)) {
			return $this->count ?? null;
		}

		$query = clone $this->queryBuilder;
		$query->select(sprintf('COUNT(DISTINCT %s)', $this->getPrimaryField()));
		$query->resetDQLPart('orderBy');
		$query->resetDQLPart('groupBy');
		$query->setFirstResult(0);
		$query->setMaxResults(null);

		foreach ($this->hints as $name => $value) {
			$query->setHint($name, $value);
		}

		$count = $query->getQuery()->getSingleScalarResult();
		return $this->count ??= (int) $count;
	}


	public function getPrimaryField(): string
	{
		return $this->checkAlias($this->primaryKey);
	}


	public function setQueryHint(string $name, mixed $value): static
	{
		$this->hints[$name] = $value;
		return $this;
	}


	/**
	 * @param  array<string, Filter> $filters
	 * @throws FilterInvalidException
	 */
	protected function filter(array $filters): void
	{
		foreach ($filters as $filter) {
			if (!$filter->isFiltered()) {
				continue;
			}

			match (true) {
				// ? Returns @true if the query matches field in the model
				$filter->hasCondition() => $filter->applyCondition($this->queryBuilder),

				$filter instanceof FilterSingle => $this->applyFilterSingle($filter),
				$filter instanceof FilterRange => $this->applyFilterRange($filter),
				$filter instanceof FilterList => $this->applyFilterList($filter),

				default => throw FilterInvalidException::unableToHandle($filter),
			};
		}
	}


	protected function filterById(int|string ...$id): void
	{
		$this->clear();

		// $this->queryBuilder->setParameters(new ArrayCollection);
		// $this->queryBuilder->resetDQLPart('where');
		// // $this->queryBuilder->resetDQLPart('join');
		// $this->placeholder = 0;

		$field = $this->getPrimaryField();
		$param = $this->getPlaceholder();

		$this->addWhere($field, "%s IN(:{$param})", [
			$param => $id,
		]);
	}


	/**
	 * @param array<string, Column> $columns
	 */
	protected function sort(array $columns): void
	{
		foreach ($columns as $name => $column) {
			if (!$column instanceof Sortable || !$sort = $column->isSorted()) {
				continue;
			}

			$field = $this->checkAlias($column->getField() ?? $name);
			$this->queryBuilder->addOrderBy($field, $sort->name);
		}

		if (! (bool) $this->queryBuilder->getDQLPart('orderBy')) {
			$this->queryBuilder->orderBy($this->getPrimaryField());
		}
	}


	protected function limit(int $offset, int $limit): void
	{
		if ($limit === 0) {
			return;
		}

		$this->queryBuilder
			->setFirstResult($offset)
			->setMaxResults($limit);
	}


	/**
	 * @return Items
	 */
	protected function fetchData(): array
	{
		$query = clone $this->queryBuilder;

		if ($query->getDQLPart('join')) {
			$query->addGroupBy($this->getPrimaryField());
			$alias = $query->getRootAliases()[0];

			foreach ($this->getOrderByFields() as $field) {
				if (str_contains($field, $alias.'.')) {
					continue;
				}

				if ($this->containsAggregateFunction($field)) {
					continue;
				}

				$query->addGroupBy($field);
			}
		}

		foreach ($this->hints as $name => $value) {
			$query->setHint($name, $value);
		}

		// ! When there is having, we cannot do count query
		if ($query->getDQLPart('having')) {
			$this->isIndeterminate = true;
		}

		/** @var Items */
		return $query->getQuery()->getResult();
	}


	protected function getPlaceholder(): string
	{
		return 'param'.($this->placeholder++);
	}


	protected function containsAggregateFunction(string $field): bool
	{
		return (bool) preg_match('/\b(?:COUNT|SUM|AVG|MIN|MAX)\s*\(/i', $field);
	}


	/**
	 * @return string[]
	 * @throws FieldInvalidException
	 */
	protected function getOrderByFields(): array
	{
		/** @var OrderBy[] */
		$fields = $this->queryBuilder->getDQLPart('orderBy');

		if (empty($fields)) {
			return [];
		}

		return array_map(array: $fields, callback: function($field): string {
			$field = (string) $field;

			if (str_contains($field, ',')) {
				throw new FieldInvalidException('Multiple orderBy columns in single statement.');
			}

			if (!$field = preg_replace('/\s+(asc|desc)/i', '', $field)) {
				throw new FieldInvalidException('Failed to remove asc|desc from orderBy fields.');
			}

			return $field;
		});
	}


	/**
	 * @throws FieldNotFoundException
	 */
	protected function checkAlias(string $field): string
	{
		// ? Search for field name inside SQL function call
		if (preg_match('/(\w+)\.(\w+)/i', $field, $match)) {
			$sql = str_replace($match[0], '%s', $field);
			[$match, $alias, $field] = $match;
		}

		if (str_contains($field, '.')) {
			[$alias, $field] = explode('.', $field, 2);
		}

		$aliases = $this->queryBuilder->getAllAliases();
		$alias ??= $aliases[0] ?? null;

		// ! InArray search is case sensitive - might cause issues
		if (!$field || !$alias || !in_array($alias, $aliases)) {
			throw FieldNotFoundException::fromName($field);
		}

		return sprintf($sql ?? '%s', $alias.'.'.$field);
	}


	/**
	 * @param array<string, mixed> $params
	 */
	protected function addWhere(string $field, string $condition, array $params = []): void
	{
		$where = strtr($condition, ['%s' => $field]);

		$qb = $this->containsAggregateFunction($field)
			? $this->queryBuilder->andHaving($where)
			: $this->queryBuilder->andWhere($where);

		foreach ($params as $param => $query) {
			$qb->setParameter($param, $query);
		}
	}


	protected function applyFilterList(Filter&FilterList $filter): void
	{
		$field = $filter->getField();

		if (!$field || !$filter->isFiltered()) {
			return;
		}

		$field = $this->checkAlias($field);
		$param = $this->getPlaceholder();

		$this->addWhere($field, "%s IN(:{$param})", [
			$param => $filter->getValue() ?? [],
		]);
	}


	protected function applyFilterRange(Filter&FilterRange $filter): void
	{
		$field = $filter->getField();

		if (!$field || !$filter->isFiltered()) {
			return;
		}

		$field = $this->checkAlias($field);
		$param = $this->getPlaceholder();

		if ($queryFrom = $filter->getValueFrom()) {
			$this->addWhere($field, "%s >= :{$param}S", [
				$param.'S' => $queryFrom,
			]);
		}

		if ($queryTo = $filter->getValueTo()) {
			$this->addWhere($field, "%s <= :{$param}E", [
				$param.'E' => $queryTo,
			]);
		}
	}


	protected function applyFilterSingle(Filter&FilterSingle $filter): void
	{
		$field = $filter->getField();

		if (!$field || !$filter->isFiltered()) {
			return;
		}

		$field = $this->checkAlias($field);
		$param = $this->getPlaceholder();

		switch (true) {
			case $filter instanceof Filters\DateFilter:
				$this->addWhere($field, "%s >= :{$param}S AND %s < :{$param}E", [
					$param.'S' => $filter->getValueFrom(),
					$param.'E' => $filter->getValueTo(),
				]);
			break;

			case $filter instanceof Filters\SelectFilter:
			case $filter instanceof Filters\EnumFilter:
				$this->addWhere($field, "%s = :{$param}", [
					$param => $filter->getValue(),
				]);
			break;

			case $filter instanceof Filters\TextFilter:
				$this->addWhere($field, "LOWER(%s) LIKE LOWER(:{$param})", [
					$param => '%'.FormatValue::string($filter->getValue()).'%'
				]);
				break;

			default: break;
		}
	}
}
