<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use BackedEnum;
use DateTimeInterface;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Source;

/**
 * @phpstan-import-type ColumnName from Column
 * @phpstan-import-type Item from Source
 * @phpstan-import-type Items from Source
 */
class ArraySource implements Source
{
	private int $totalCount;
	private string $primaryKey;

	/**
	 * @param Items $items
	 */
	public function __construct(
		private array $items,
	) {
		$this->totalCount = sizeof($items);
	}


	public function setPrimaryKey(string $primaryKey): self
	{
		$this->primaryKey = $primaryKey;
		return $this;
	}


	public function getPrimaryKey(): ?string
	{
		return $this->primaryKey ?? null;
	}


	public function totalCount(): int
	{
		return $this->totalCount;
	}


	/**
	 * @return Items
	 */
	public function fetchItems(): iterable
	{
		// todo: handle onDataLoaded event in the Source
		return $this->items;
	}


	/**
	 * @param array<string, Filter> $filters
	 */
	public function filter(array $filters): void
	{
		$items = $conditions = [];

		foreach ($filters as $filter) {
			if (!$filter->isFiltered()) {
				continue;
			}

			$conditions = array_merge($conditions, $filter->getConditions());
		}

		if (empty($conditions)) {
			return;
		}

		foreach ($this->items as $key => $item) {
			$row = new Row($item, $this->primaryKey);

			foreach ($conditions as $field => $query) {
				$value = $row->getValue($field);
				// todo: query should be scalar|scalar[]
				$query = (string) $query; // @phpstan-ignore cast.string

				if (is_string($value) && strcasecmp($query, $value) <> 0) {
					continue;

				} elseif (!is_string($value) && $query <> $value) {
					continue;
				}

				$items[$key] = $item;
				break;
			}
		}

		$this->items = $items;
	}


	public function filterById(int|string ...$rows): void
	{
		$items = [];

		foreach ($this->items as $key => $item) {
			$row = new Row($item, $this->primaryKey);

			if (!in_array($row->getId(), $rows)) {
				continue;
			}

			$items[$key] = $item;
		}

		$this->items = $items;
	}


	/**
	 * todo: implement custom sorting  -->  https://stackoverflow.com/questions/2699086/sort-a-2d-array-by-a-column-value
	 * @param array<ColumnName, Column> $columns
	 */
	public function sort(array $columns): void
	{
		foreach ($columns as $name => $column) {
			if (!$column instanceof Sortable) {
				continue;
			}

			if (!$sort = $column->isSorted()) {
				continue;
			}

			$field = $column->getSortedBy() ?? $name;
			$items = $result = [];

			foreach ($this->items as $key => $item) {
				$row = new Row($item, $this->primaryKey);
				$value = $row->getValue($field) ?? '';

				$sortBy = match (true) {
					$value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
					$value instanceof BackedEnum => $value->value,
					default => (string) $value,	// @phpstan-ignore cast.string
				};

				$result[$sortBy][$key] = $item;
			}

			if ($sort === Sort::ASC) {
				ksort($result, SORT_LOCALE_STRING);
			} else {
				krsort($result, SORT_LOCALE_STRING);
			}

			foreach ($result as $sortBy => $rows) {
				foreach ($rows as $key => $item) {
					$items[$key] = $item;
				}
			}

			$this->items = $items;
		}
	}


	public function limit(int $page, int $limit): void
	{
		$this->items = array_slice($this->items, $limit * ($page - 1), $limit, true);
	}
}
