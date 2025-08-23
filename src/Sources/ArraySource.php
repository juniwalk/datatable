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
use JuniWalk\DataTable\Exceptions\FilterUnknownException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters\DateFilter;
use JuniWalk\DataTable\Filters\DateRangeFilter;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Source;
use JuniWalk\DataTable\Tools\FormatValue;
use JuniWalk\Utils\Format;

/**
 * @phpstan-import-type Items from Source
 */
class ArraySource extends AbstractSource
{
	protected int $count;


	/**
	 * @param Items $items
	 */
	public function __construct(
		protected array $items,
		protected string $primaryKey = 'id',
	) {
		$this->count = sizeof($items);
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
		return $this->count;
	}


	/**
	 * @param  array<string, Filter> $filters
	 * @throws FilterUnknownException
	 */
	protected function filter(array $filters): void
	{
		if (empty($filters) || empty($this->items)) {
			return;
		}

		foreach ($this->items as $key => $item) {
			$row = new Row($item, $this);

			foreach ($filters as $filter) {
				if (!$filter->isFiltered()) {
					continue;
				}

				$isMatching = match (true) {
					// ? Returns @true if the query matches field in the model
					$filter->hasCondition() => $filter->applyCondition($item),

					$filter instanceof DateRangeFilter,
					$filter instanceof DateFilter => $this->applyDateFilter($row, $filter),

					default => $this->applyTextFilter($row, $filter),
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
			$row = new Row($item, $this);

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
				$value = (new Row($item, $this))->getValue($name);
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
	protected function getData(): iterable
	{
		return $this->items;
	}


	/**
	 * @throws FilterUnknownException
	 */
	protected function applyTextFilter(Row $row, Filter $filter): bool
	{
		$query = $filter->getValueFormatted();

		if (!$filter->isFiltered() || !$query) {
			return false;
		}

		if (is_array($query)) {
			throw FilterUnknownException::fromFilter($filter);
		}

		foreach ($filter->getColumns() as $column) {
			$value = FormatValue::string($row->getValue($column));

			if (strcasecmp((string) $query, $value ?? '') <> 0) {
				return true;
			}
		}

		return false;
	}


	protected function applyDateFilter(Row $row, DateFilter|DateRangeFilter $filter): bool
	{
		$queryFrom = $filter->getValueFrom();
		$queryTo = $filter->getValueTo();

		if (!$filter->isFiltered() || !($queryFrom && $queryTo)) {
			return false;
		}

		foreach ($filter->getColumns() as $column) {
			$value = FormatValue::dateTime($row->getValue($column));

			if ($value >= $queryFrom && $value <= $queryTo) {
				return true;
			}
		}

		return false;
	}
}
