<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Source;

/**
 * @phpstan-import-type ColumnName from Column
 * @phpstan-import-type Item from Source
 * @phpstan-import-type Items from Source
 */
class ArraySource implements Source
{
	private int $totalCount;

	/**
	 * @param Items $items
	 */
	public function __construct(
		private array $items,
	) {
		$this->totalCount = sizeof($items);
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
	 * @param array<ColumnName, scalar> $filters
	 */
	public function filter(array $filters): void
	{
		// todo: implement custom filtering
		foreach ($filters as $column => $query) {
			$query = (string) $query;
			$items = [];

			// todo: dont use index, get $primaryKey from $item
			foreach ($this->items as $id => $item) {
				$value = $this->readValue($item, $column) ?? null;

				if (is_string($value) && strcasecmp($query, $value)) {
					continue;

				} elseif ($query <> $value) {
					continue;
				}

				$items[$id] = $item;
			}

			$this->items = $items;
		}
	}


	public function filterOne(int|string ...$rows): void
	{
		$items = [];

		// todo: dont use index, get $primaryKey from $item
		foreach ($this->items as $id => $item) {
			if (!in_array($id, $rows)) {
				continue;
			}

			$items[$id] = $item;
		}

		$this->items = $items;
	}


	/**
	 * @param array<ColumnName, Column> $columns
	 */
	public function sort(array $columns): void
	{
		// todo: implement custom sorting  -->  https://stackoverflow.com/questions/2699086/sort-a-2d-array-by-a-column-value

		foreach ($columns as $name => $column) {
			if (!$sort = $column->isSorted()) {
				continue;
			}

			$field = $column->getSortedBy() ?? $name;
			$items = [];

			// todo: dont use index, get $primaryKey from $item
			foreach ($this->items as $id => $item) {
				$value = $this->readValue($item, $field) ?? '';
				$sort_by = $value instanceof \DateTimeInterface
					? $value->format('Y-m-d H:i:s')
					: (string) $value; // @phpstan-ignore cast.string

				$items[$sort_by][$id] = $item;
			}

			if ($sort === Sort::ASC) {
				ksort($items, SORT_LOCALE_STRING);
			} else {
				krsort($items, SORT_LOCALE_STRING);
			}

			$dataSource = [];

			foreach ($items as $list) {
				foreach ($list as $id => $item) {
					$dataSource[$id] = $item;
				}
			}

			$this->items = $dataSource;
		}
	}


	public function limit(int $page, int $limit): void
	{
		$this->items = array_slice($this->items, $limit * ($page - 1), $limit, true);
	}


	/**
	 * @param Item $item
	 */
	private function readValue(array|object $item, string $field): mixed
	{
		return match (true) {
			is_object($item) => $item->$field ?? null,
			is_array($item) => $item[$field] ?? null,	// @phpstan-ignore function.alreadyNarrowedType

			// todo: throw InvalidValueException
			default => throw new \Exception,
		};
	}
}
