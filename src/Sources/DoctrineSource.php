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
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters;
use JuniWalk\DataTable\Source;
use JuniWalk\Utils\Strings;

/**
 * @phpstan-import-type Item from Source
 * @phpstan-import-type Items from Source
 */
class DoctrineSource implements Source
{
	/** @var array<string, mixed> */
	protected array $hints = [];

	public function __construct(
		protected QueryBuilder $queryBuilder,
		protected string $primaryKey = 'id',
	) {
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

				// todo: UnknownFilterException
				default => throw new \Exception,
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

			$field = $column->getSortedBy() ?? $name;
			$field = $this->checkAlias($field);

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


	protected function checkAlias(string $column): string
	{
		if (str_contains($column, '.')) {
			[$alias, $column] = explode('.', $column, 2);
		}

		$aliases = $this->queryBuilder->getAllAliases();
		$alias ??= $aliases[0] ?? null;

		if (!$alias || !in_array($alias, $aliases)) {
			// todo: throw
			throw new \Exception;
		}

		return $alias.'.'.$column;
	}


	protected function getQuery(): Query
	{
		$query = $this->queryBuilder->getQuery();

		foreach ($this->hints as $name => $value) {
			$query->setHint($name, $value);
		}

		return $query;
	}


	protected function applyDateFilter(Filters\DateFilter $filter): void
	{
		$query = $filter->getValue();

		if (!is_string($query)) {
			// todo:
			throw new \Exception;
		}

		$start = new \DateTime($query)->modify('midnight');
		$end = (clone $start)->modify('+1 day');

		foreach ($filter->getColumns() as $column) {
			$field = $this->checkAlias($column);
			$key = Strings::webalize($filter->getName().$column);

			// WHERE e.date >= :start AND e.date < :end
			$this->queryBuilder->andWhere($field.' >= :'.$key.'S AND '.$field.' < :'.$key.'E')
				->setParameter($key.'S', $start)
				->setParameter($key.'E', $end);
		}
	}


	protected function applyEnumFilter(Filters\EnumFilter $filter): void
	{
		$query = $filter->getValue();

		foreach ($filter->getColumns() as $column) {
			$field = $this->checkAlias($column);
			$key = Strings::webalize($filter->getName().$column);

			$this->queryBuilder->andWhere('LOWER('.$field.') = LOWER(:'.$key.')')
				->setParameter($key, $query);
		}
	}


	protected function applyTextFilter(Filters\TextFilter $filter): void
	{
		$query = $filter->getValue();

		if (!is_string($query)) {
			// todo:
			throw new \Exception;
		}

		foreach ($filter->getColumns() as $column) {
			$field = $this->checkAlias($column);
			$key = Strings::webalize($filter->getName().$column);

			$this->queryBuilder->andWhere('LOWER('.$field.') LIKE LOWER(:'.$key.')')
				->setParameter($key, '%'.$query.'%');
		}
	}
}
