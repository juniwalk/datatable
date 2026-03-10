<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Exceptions\FilterInvalidException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Filters\Interfaces\FilterRange;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\DataTable\Source;
use JuniWalk\DataTable\Sources\AbstractSource;
use JuniWalk\DataTable\Tools\FormatValue;

/**
 * @phpstan-import-type Items from Source
 */
class DoctrineDBALSource extends AbstractSource
{
	protected readonly QueryBuilder $copy;

	/** @var array<string, ?Sort> */
	protected array $orderBy = [];

	public function __construct(
		protected QueryBuilder $queryBuilder,
		protected string $primaryKey = 'id',
	) {
		// ? Custom orderBy not allowed as it cannot be accessed
		$queryBuilder->resetOrderBy();

		$this->copy = clone $queryBuilder;
	}


	public static function isModel(mixed $model): bool
	{
		return $model instanceof QueryBuilder;
	}


	public function clear(): void
	{
		$this->count = $this->countOnPage = null;
		$this->queryBuilder = clone $this->copy;
		$this->orderBy = [];
	}


	public function getCount(): ?int
	{
		if ($this->isIndeterminate || isset($this->count)) {
			return $this->count ?? null;
		}

		$query = clone $this->queryBuilder;
		$query->select(sprintf('COUNT(DISTINCT %s)', $this->primaryKey));
		$query->resetOrderBy();
		$query->resetGroupBy();
		$query->setFirstResult(0);
		$query->setMaxResults(null);

		$result = $query->fetchOne();

		if (is_numeric($result)) {
			$count = (int) $result;
		}

		return $this->count ??= $count ?? null;
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
		$this->queryBuilder->setParameters([]);
		$this->queryBuilder->resetWhere();

		$param = $this->queryBuilder->createNamedParameter(...);
		$this->andWhere('%field% IN(%query%)', [
			'field' => $this->primaryKey,
			'query' => $param($id, ArrayParameterType::STRING),
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

			$field = $column->getField() ?? $name;
			$this->orderBy[$field] = $sort;
		}

		if (empty($this->orderBy)) {
			$this->orderBy[$this->primaryKey] = Sort::ASC;
		}

		foreach ($this->orderBy as $field => $sort) {
			$this->queryBuilder->addOrderBy($field, $sort?->name);
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

		// todo: this will cause issues as MSSQL requires all columns to be in groupBy or aggregate
		if (str_contains('JOIN', $query->getSQL())) {
			$query->addGroupBy($this->primaryKey);

			foreach ($this->orderBy as $field => $sort) {
				$query->addGroupBy($field);
			}
		}

		return $query->fetchAllAssociative();
	}


	/**
	 * @param array<string, mixed> $params
	 */
	protected function andWhere(string $condition, array $params): void
	{
		$tokens = [];

		foreach ($params as $key => $value) {
			$tokens['%'.$key.'%'] = $value;
		}

		$this->queryBuilder->andWhere(
			strtr($condition, $tokens)
		);
	}


	protected function applyFilterList(Filter&FilterList $filter): void
	{
		$query = $filter->getValue() ?? [];
		$field = $filter->getField();

		if (!$field || !$filter->isFiltered()) {
			return;
		}

		$param = $this->queryBuilder->createNamedParameter(...);
		$this->andWhere('%field% IN(%query%)', [
			'field' => $field,
			'query' => $param($query, ArrayParameterType::STRING),
		]);
	}


	protected function applyFilterRange(Filter&FilterRange $filter): void
	{
		$field = $filter->getField();

		if (!$field || !$filter->isFiltered()) {
			return;
		}

		$param = $this->queryBuilder->createNamedParameter(...);

		if ($queryFrom = $filter->getValueFrom()) {
			$this->andWhere('%field% >= %queryS%', [
				'field' => $field,
				'queryS' => $param($queryFrom),
			]);
		}

		if ($queryTo = $filter->getValueTo()) {
			$this->andWhere('%field% <= %queryE%', [
				'field' => $field,
				'queryE' => $param($queryTo),
			]);
		}
	}


	protected function applyFilterSingle(Filter&FilterSingle $filter): void
	{
		$query = $filter->getValue();
		$field = $filter->getField();

		if (!$field || !$filter->isFiltered()) {
			return;
		}

		$param = $this->queryBuilder->createNamedParameter(...);

		switch (true) {
			case $filter instanceof Filters\DateFilter:
				$this->andWhere('%field% >= %queryS% AND %field% < %queryE%', [
					'field' => $field,
					'queryS' => $param($filter->getValueFrom()),
					'queryE' => $param($filter->getValueTo()),
				]);
			break;

			case $filter instanceof Filters\SelectFilter:
			case $filter instanceof Filters\EnumFilter:
				$this->andWhere('%field% = %query%', [
					'field' => $field,
					'query' => $param($query),
				]);
			break;

			case $filter instanceof Filters\TextFilter:
				$this->andWhere('LOWER(%field%) LIKE LOWER(%query%)', [
					'field' => $field,
					'query' => $param('%'.FormatValue::string($query).'%'),
				]);
				break;

			default: break;
		}
	}
}
