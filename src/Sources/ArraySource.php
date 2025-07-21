<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Source;

/**
 * @phpstan-import-type Item from Source
 * @phpstan-import-type Items from Source
 */
class ArraySource implements Source
{
	private int $count;

	/**
	 * @param Items $items
	 */
	public function __construct(
		private array $items,
	) {
		$this->count = sizeof($items);
	}


	/**
	 * @return Items
	 */
	public function getItems(): iterable
	{
		return $this->items;
	}


	public function getCount(): int
	{
		return $this->count;
	}


	/**
	 * @param array<non-empty-string, scalar> $filter
	 */
	public function filter(array $filter): void
	{
		// todo: implement custom filtering
		foreach ($filter as $column => $query) {
			$query = (string) $query;
			$items = [];

			foreach ($this->items as $id => $item) {
				$value = (string) ($item[$column] ?? ''); // @phpstan-ignore cast.string

				if (strcasecmp($query, $value)) {
					continue;
				}

				$items[$id] = $item;
			}

			$this->items = $items;
		}
	}


	/**
	 * @param array<non-empty-string, Sort|'asc'|'desc'|null> $sort
	 */
	public function sort(array $sort): void
	{
		// todo: implement custom sorting  -->  https://stackoverflow.com/questions/2699086/sort-a-2d-array-by-a-column-value
		foreach ($sort as $column => $order) {
			$order = Sort::make($order);
			$items = [];

			foreach ($this->items as $id => $item) {
				$value = is_object($item) ? $item->$column : $item[$column];
				$sort_by = $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : (string) $value; // @phpstan-ignore cast.string

				$items[$sort_by][$id] = $item;
			}

			if ($order === Sort::ASC) {
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
}
