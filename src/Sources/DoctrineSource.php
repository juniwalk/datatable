<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
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
class DoctrineSource extends AbstractSource
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


	public function clear(): void
	{
		$this->placeholder = sizeof($this->copy->getParameters());
		$this->queryBuilder = clone $this->copy;
		$this->countOnPage = null;
		$this->count = null;
	}


	public function getCount(): ?int
	{
		if ($this->isIndeterminate) {
			return null;
		}

		return $this->count ??= (int) $this->getQueryCount()->getSingleScalarResult();
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
		$this->queryBuilder->setParameters(new ArrayCollection);
		$this->queryBuilder->resetDQLPart('where');
		$this->placeholder = 0;

		$field = $this->getPrimaryField();
		$param = $this->getPlaceholder();

		$this->queryBuilder->andWhere("{$field} IN(:{$param})")
			->setParameter($param, $id);
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
		/** @var Items */
		return $this->getQuery()->getResult();
	}


	protected function getQuery(): Query
	{
		$qb = clone $this->queryBuilder;
		$qb->addGroupBy($this->getPrimaryField());

		$alias = $qb->getRootAliases()[0];

		foreach ($this->getOrderByFields() as $field) {
			if (str_contains($field, $alias.'.')) {
				continue;
			}

			$qb->addGroupBy($field);
		}

		$query = $qb->getQuery();

		foreach ($this->hints as $name => $value) {
			$query->setHint($name, $value);
		}

		return $query;
	}


	protected function getQueryCount(): Query
	{
		$count = clone $this->queryBuilder;
		$count->select(sprintf('COUNT(DISTINCT %s)', $this->getPrimaryField()));
		$count->resetDQLPart('orderBy');
		$count->resetDQLPart('groupBy');
		$count->setFirstResult(0);
		$count->setMaxResults(null);

		$query = $count->getQuery();

		foreach ($this->hints as $name => $value) {
			$query->setHint($name, $value);
		}

		return $query;
	}


	protected function getPlaceholder(): string
	{
		return 'param'.($this->placeholder++);
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

			// ! This seems like insufficient way of detecting multiple columns
			if (substr_count('.', $field) > 1) {
				throw new FieldInvalidException('Multiple orderBy columns in single statement.');
			}

			if (!$field = preg_replace('/\s(asc|desc)/i', '', $field)) {
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


	protected function applyFilterList(Filter&FilterList $filter): void
	{
		$field = $filter->getField();

		if (!$field || !$filter->isFiltered()) {
			return;
		}

		$query = $filter->getValue() ?? [];
		$field = $this->checkAlias($field);
		$param = $this->getPlaceholder();

		$this->queryBuilder->andWhere("{$field} IN(:{$param})")
			->setParameter($param, $query);
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
			$this->queryBuilder->andWhere("{$field} >= :{$param}S")
				->setParameter($param.'S', $queryFrom);
		}

		if ($queryTo = $filter->getValueTo()) {
			$this->queryBuilder->andWhere("{$field} < :{$param}E")
				->setParameter($param.'E', $queryTo);
		}
	}


	protected function applyFilterSingle(Filter&FilterSingle $filter): void
	{
		$field = $filter->getField();

		if (!$field || !$filter->isFiltered()) {
			return;
		}

		$query = $filter->getValue();
		$field = $this->checkAlias($field);
		$param = $this->getPlaceholder();

		switch (true) {
			case $filter instanceof Filters\DateFilter:
				$this->queryBuilder->andWhere("{$field} >= :{$param}S AND {$field} < :{$param}E")
					->setParameter($param.'S', $filter->getValueFrom())
					->setParameter($param.'E', $filter->getValueTo());
			break;

			case $filter instanceof Filters\SelectFilter:
			case $filter instanceof Filters\EnumFilter:
				$this->queryBuilder->andWhere("{$field} = :{$param}")
					->setParameter($param, $query);
			break;

			case $filter instanceof Filters\TextFilter:
				$this->queryBuilder->andWhere("LOWER({$field}) LIKE LOWER(:{$param})")
					->setParameter($param, '%'.FormatValue::string($query).'%');
				break;

			default: break;
		}
	}
}
