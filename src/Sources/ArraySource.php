<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Source;

class ArraySource implements Source
{
	private int $count;

	public function __construct(
		private array $items,
	) {
		$this->count = sizeof($items);
	}


	public function getItems(): iterable
	{
		return $this->items;
	}


	public function getCount(): int
	{
		return $this->count;
	}


	// todo: add docBlock with types
	public function filter(array $filter): void
	{
		foreach ($filter as $column => $query) {
			$items = [];

			foreach ($this->items as $id => $item) {

				if (strcasecmp($query, $item[$column])) {
					continue;
				}

				$items[$id] = $item;
			}

			$this->items = $items;
		}
	}


	/**
	 * @param array<string, Sort> $sort
	 */
	public function sort(array $sort): void
	{
		// todo: implement custom sorting  -->  https://stackoverflow.com/questions/2699086/sort-a-2d-array-by-a-column-value
		foreach ($sort as $column => $order) {
			$items = [];

			foreach ($this->items as $id => $item) {
				$value = is_object($item) ? $item->$column : $item[$column]; // @phpstan-ignore-line
				$sort_by = $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : (string) $value;

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
