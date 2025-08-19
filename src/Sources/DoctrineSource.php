<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use BackedEnum;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Exceptions\FilterUnknownException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters;
use JuniWalk\DataTable\Source;
use Stringable;

/**
 * @phpstan-import-type Items from Source
 */
class DoctrineSource extends AbstractSource
{
	protected int $placeholder;
	protected int $count;

	/** @var array<string, mixed> */
	protected array $hints = [];


	public function __construct(
		protected QueryBuilder $queryBuilder,
		protected string $primaryKey = 'id',
	) {
		$this->placeholder = sizeof($queryBuilder->getParameters());
	}


	public function setPrimaryKey(string $primaryKey): self
	{
		$this->primaryKey = $primaryKey;
		return $this;
	}


	public function getPrimaryKey(): string
	{
		return $this->primaryKey;
	}


	public function getCount(): int
	{
		return $this->count ??= (int) $this->getQueryCount()->getSingleScalarResult();
	}


	public function getPrimaryField(): string
	{
		return $this->checkAlias($this->primaryKey);
	}


	public function setQueryHint(string $name, mixed $value): self
	{
		$this->hints[$name] = $value;
		return $this;
	}


	/**
	 * @param  array<string, Filter> $filters
	 * @throws FilterUnknownException
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

				$filter instanceof Filters\DateFilter => $this->applyDateFilter($filter),
				$filter instanceof Filters\EnumFilter => $this->applyEnumFilter($filter),
				$filter instanceof Filters\TextFilter => $this->applyTextFilter($filter),

				default => throw FilterUnknownException::fromFilter($filter),
			};
		}
	}


	protected function filterOne(int|string $id): void
	{
		$this->queryBuilder->setParameters(new ArrayCollection);
		$this->queryBuilder->resetDQLPart('where');
		$this->placeholder = 0;

		$field = $this->getPrimaryField();
		$param = $this->getPlaceholder();

		$this->queryBuilder->andWhere("{$field} = :{$param}")
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
	protected function getData(): iterable
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
			if (str_starts_with($field, $alias.'.')) {
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
	 */
	protected function getOrderByFields(): array
	{
		/** @var Stringable[] */
		$fields = $this->queryBuilder->getDQLPart('orderBy');

		if (empty($fields)) {
			return [];
		}

		$fields = explode(', ', implode(', ', $fields));
		$fields = array_map(array: $fields, callback: function($field): ?string {
			return preg_replace('/\s(asc|desc)$/i', '', $field);
		});

		return array_filter($fields);
	}


	/**
	 * @throws FieldNotFoundException
	 */
	protected function checkAlias(string $field): string
	{
		if (str_contains($field, '.')) {
			[$alias, $field] = explode('.', $field, 2);
		}

		$aliases = $this->queryBuilder->getAllAliases();
		$alias ??= $aliases[0] ?? null;

		// ? InArray search is case sensitive - might cause issues
		if (!$field || !$alias || !in_array($alias, $aliases)) {
			throw FieldNotFoundException::fromName($field);
		}

		return $alias.'.'.$field;
	}


	protected function applyDateFilter(Filters\DateFilter $filter): void
	{
		if (!$query = $filter->getValue()) {
			return;
		}

		$start = DateTime::createFromInterface($query)->modify('midnight');
		$end = (clone $start)->modify('+1 day');

		foreach ($filter->getColumns() as $name => $column) {
			$field = $this->checkAlias($column->getField() ?? $name);
			$param = $this->getPlaceholder();

			$this->queryBuilder->andWhere("{$field} >= :{$param}S AND {$field} < :{$param}E")
				->setParameter($param.'S', $start)
				->setParameter($param.'E', $end);
		}
	}


	/**
	 * @template T of BackedEnum
	 * @param Filters\EnumFilter<T> $filter
	 */
	protected function applyEnumFilter(Filters\EnumFilter $filter): void
	{
		if (!$query = $filter->getValue()) {
			return;
		}

		foreach ($filter->getColumns() as $name => $column) {
			$field = $this->checkAlias($column->getField() ?? $name);
			$param = $this->getPlaceholder();

			$this->queryBuilder->andWhere("LOWER({$field}) = LOWER(:{$param})")
				->setParameter($param, $query);
		}
	}


	protected function applyTextFilter(Filters\TextFilter $filter): void
	{
		if (!$query = $filter->getValue()) {
			return;
		}

		foreach ($filter->getColumns() as $name => $column) {
			$field = $this->checkAlias($column->getField() ?? $name);
			$param = $this->getPlaceholder();

			$this->queryBuilder->andWhere("LOWER({$field}) LIKE LOWER(:{$param})")
				->setParameter($param, '%'.$query.'%');
		}
	}
}
