<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Exceptions\FilterInvalidException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Filters\Interfaces\FilterRange;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Source;
use JuniWalk\DataTable\Tools\Compare;
use JuniWalk\Utils\Format;

/**
 * @phpstan-import-type FilterStruct from Filter
 * @phpstan-import-type Items from Source
 */
class ArraySource extends AbstractSource
{
	/**
	 * @param Items $items
	 */
	public function __construct(
		protected array $items,
		protected string $primaryKey = 'id',
	) {
		$this->count = sizeof($items);
	}


	/**
	 * @param  array<string, FilterStruct> $filters
	 * @throws FilterInvalidException
	 */
	protected function filter(array $filters): void
	{
		if (empty($filters) || empty($this->items)) {
			return;
		}

		foreach ($this->items as $key => $item) {
			$row = new Row($item, $this->primaryKey);

			foreach ($filters as $filter) {
				if (!$filter->isFiltered()) {
					continue;
				}

				$isMatching = match (true) {
					// ? Returns @true if the query matches field in the model
					$filter->hasCondition() => $filter->applyCondition($item),

					$filter instanceof FilterSingle => $this->applyFilterSingle($filter, $row),
					$filter instanceof FilterRange => $this->applyFilterRange($filter, $row),
					$filter instanceof FilterList => $this->applyFilterList($filter, $row),

					default => throw FilterInvalidException::unableToHandle($filter),
				};

				if ($isMatching) {
					continue;
				}

				unset($this->items[$key]);
			}
		}

		$this->count = sizeof($this->items);
	}


	protected function filterOne(int|string $id): void
	{
		$items = [];

		foreach ($this->items as $key => $item) {
			$row = new Row($item, $this->primaryKey);

			if ($id <> $row->getId()) {
				continue;
			}

			$items[$key] = $item;
			break;
		}

		$this->items = $items;
	}


	/**
	 * @param  array<string, Column> $columns
	 * @throws FieldNotFoundException
	 */
	protected function sort(array $columns): void
	{
		if (empty($columns) || empty($this->items)) {
			return;
		}

		$ordering = [];

		foreach ($columns as $name => $column) {
			if (!$column instanceof Sortable || !$sort = $column->isSorted()) {
				continue;
			}

			$ordering[] = array_map(array: $this->items, callback: function($item) use ($name): string {
				$value = (new Row($item, $this->primaryKey))->getValue($name);
				return Format::stringify($value);
			});

			$ordering[] = $sort->order();
			$ordering[] = match (true) {
				$column instanceof Columns\NumberColumn => SORT_NUMERIC,
				$column instanceof Columns\DateColumn,
				$column instanceof Columns\EnumColumn => SORT_NATURAL,
				default => SORT_LOCALE_STRING,
			};
		}

		$ordering[] = &$this->items;

		array_multisort(...$ordering);	// @phpstan-ignore argument.type (not true)
	}


	protected function limit(int $offset, int $limit): void
	{
		if ($limit === 0) {
			return;
		}

		$this->items = array_slice($this->items, $offset, $limit, true);
	}


	/**
	 * @return Items
	 */
	protected function fetchData(): array
	{
		return $this->items;
	}


	protected function applyFilterList(Filter&FilterList $filter, Row $row): bool
	{
		if (!$filter->isFiltered()) {
			return false;
		}

		$query = $filter->getValue() ?? [];

		foreach ($filter->getColumns() as $column) {
			$value = $row->getValue($column);

			if (in_array($value, $query)) {
				return true;
			}
		}

		return false;
	}


	protected function applyFilterRange(Filter&FilterRange $filter, Row $row): bool
	{
		if (!$filter->isFiltered()) {
			return false;
		}

		$queryFrom = $filter->getValueFrom();
		$queryTo = $filter->getValueTo();

		foreach ($filter->getColumns() as $column) {
			$value = $row->getValue($column);

			if ($queryFrom && $value < $queryFrom) {
				continue;
			}

			if ($queryTo && $value >= $queryTo) {
				continue;
			}

			return true;
		}

		return false;
	}


	protected function applyFilterSingle(Filter&FilterSingle $filter, Row $row): bool
	{
		if (!$filter->isFiltered()) {
			return false;
		}

		$query = $filter->getValue();

		foreach ($filter->getColumns() as $column) {
			$value = $row->getValue($column);
			$isMatching = match (true) {
				$filter instanceof Filters\DateFilter => Compare::date($value, $query),
				$filter instanceof Filters\EnumFilter => Compare::enum($value, $query, $filter->getEnumType()),
				$filter instanceof Filters\TextFilter => Compare::string($value, $query),

				default => $value == $query,
			};

			if ($isMatching) {
				return true;
			}
		}

		return false;
	}
}
