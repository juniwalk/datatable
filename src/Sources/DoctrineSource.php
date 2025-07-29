<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Exceptions\FilterUnknownException;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters;
use JuniWalk\DataTable\Source;

/**
 * @phpstan-import-type Item from Source
 * @phpstan-import-type Items from Source
 */
class DoctrineSource implements Source
{
	/** @var array<string, mixed> */
	protected array $hints = [];

	protected int $placeholder;

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


	public function getPrimaryField(): string
	{
		return $this->checkAlias($this->primaryKey);
	}


	public function setQueryHint(string $name, mixed $value): self
	{
		$this->hints[$name] = $value;
		return $this;
	}


	public function totalCount(): int
	{
		$source = clone $this->queryBuilder;
		$source->select(sprintf('COUNT(%s)', $this->getPrimaryField()));
		$source->resetDQLPart('orderBy');
		$source->setFirstResult(0);

		// todo: this needs to be cached
		return (int) $source->getQuery()
			->getSingleScalarResult();
	}


	/**
	 * @return Items
	 */
	public function fetchItems(): iterable
	{
		// todo: handle onDataLoaded event in the Source

		/** @var Items */
		return $this->getQuery()->getResult();
	}


	/**
	 * @param array<string, Filter> $filters
	 */
	public function filter(array $filters): void
	{
		foreach ($filters as $filter) {
			if (!$filter->isFiltered()) {
				continue;
			}

			// todo: handle custom filter condition

			match (true) {
				$filter instanceof Filters\DateFilter => $this->applyDateFilter($filter),
				$filter instanceof Filters\EnumFilter => $this->applyEnumFilter($filter),
				$filter instanceof Filters\TextFilter => $this->applyTextFilter($filter),

				default => throw FilterUnknownException::fromFilter($filter),
			};
		}
	}


	public function filterById(int|string ...$rows): void
	{
		$this->queryBuilder->setParameters(new ArrayCollection);
		$this->queryBuilder->resetDQLPart('where');

		$field = $this->getPrimaryField();
		$this->queryBuilder->where($field.' IN(:list)')
			->setParameter('list', $rows);
	}


	/**
	 * @param array<string, Column> $columns
	 */
	public function sort(array $columns): void
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


	public function limit(int $page, int $limit): void
	{
		if ($limit === 0) {
			return;
		}

		$offset = $limit * ($page - 1);

		$this->queryBuilder
			->setFirstResult($offset)
			->setMaxResults($limit);
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


	protected function getQuery(): Query
	{
		$query = $this->queryBuilder->getQuery();

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
	 * @throws FilterValueInvalidException
	 */
	protected function applyDateFilter(Filters\DateFilter $filter): void
	{
		$query = $filter->getValue();

		if (!is_string($query)) {
			throw FilterValueInvalidException::fromFilter($filter, 'string');
		}

		$start = new \DateTime($query)->modify('midnight');
		$end = (clone $start)->modify('+1 day');

		foreach ($filter->getColumns() as $name => $column) {
			$field = $this->checkAlias($column->getField() ?? $name);
			$param = $this->getPlaceholder();

			$this->queryBuilder->andWhere("{$field} >= :{$param}S AND {$field} < :{$param}E")
				->setParameter($param.'S', $start)
				->setParameter($param.'E', $end);
		}
	}


	protected function applyEnumFilter(Filters\EnumFilter $filter): void
	{
		$query = $filter->getValue();

		// todo: check value

		foreach ($filter->getColumns() as $name => $column) {
			$field = $this->checkAlias($column->getField() ?? $name);
			$param = $this->getPlaceholder();

			$this->queryBuilder->andWhere("LOWER({$field}) = LOWER(:{$param})")
				->setParameter($param, $query);
		}
	}


	/**
	 * @throws FilterValueInvalidException
	 */
	protected function applyTextFilter(Filters\TextFilter $filter): void
	{
		$query = $filter->getValue();

		if (!is_string($query)) {
			throw FilterValueInvalidException::fromFilter($filter, 'string');
		}

		foreach ($filter->getColumns() as $name => $column) {
			$field = $this->checkAlias($column->getField() ?? $name);
			$param = $this->getPlaceholder();

			$this->queryBuilder->andWhere("LOWER({$field}) LIKE LOWER(:{$param})")
				->setParameter($param, '%'.$query.'%');
		}
	}
}
