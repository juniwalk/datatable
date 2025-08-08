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
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Source;
use JuniWalk\Utils\Format;

/**
 * @phpstan-import-type Items from Source
 */
class ArraySource extends AbstractSource
{
	private int $totalCount;


	/**
	 * @param Items $items
	 */
	public function __construct(
		private array $items,
		private string $primaryKey = 'id',
	) {
		parent::__construct();
		$this->totalCount = sizeof($items);
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


	/**
	 * @param array<string, Filter> $filters
	 */
	public function filter(array $filters): void
	{
		if (empty($this->items)) {
			return;
		}

		foreach ($this->items as $key => $item) {
			$row = new Row($item, $this);

			foreach ($filters as $filter) {
				if (!$filter->isFiltered()) {
					continue;
				}

				// todo: handle custom filter condition

				if (!$this->isMatching($row, $filter)) {
					continue;
				}

				unset($this->items[$key]);
			}
		}

		$this->totalCount = sizeof($this->items);
	}


	public function filterById(int|string ...$rows): void
	{
		$items = [];

		foreach ($this->items as $key => $item) {
			$row = new Row($item, $this);

			if (!in_array($row->getId(), $rows)) {
				continue;
			}

			$items[$key] = $item;
		}

		$this->items = $items;
	}


	/**
	 * @param  array<string, Column> $columns
	 * @throws FieldNotFoundException
	 */
	public function sort(array $columns): void
	{
		if (empty($this->items)) {
			return;
		}

		foreach ($columns as $name => $column) {
			if (!$column instanceof Sortable || !$sort = $column->isSorted()) {
				continue;
			}

			$field = $column->getField() ?? $name;
			$type = match (true) {
				$column instanceof Columns\NumberColumn => SORT_NUMERIC,
				$column instanceof Columns\DateColumn,
				$column instanceof Columns\EnumColumn => SORT_NATURAL,
				default => SORT_LOCALE_STRING,
			};

			// todo: try to use Row::getValue to get the keys for sorting (to be more universal)
			$keys = array_map(
				fn($key) => Format::stringify($key),
				array_column($this->items, $field),
			);

			if (empty($keys)) {
				// todo: if Row::getValue is used, this is gonna be thrown there instead
				throw FieldNotFoundException::fromName($field);
			}

			array_multisort($keys, $sort->order(), $type, $this->items);
		}
	}


	public function limit(int $offset, int $limit): void
	{
		if ($limit === 0) {
			return;
		}

		$this->items = array_slice($this->items, $offset, $limit, true);
	}


	public function totalCount(): int
	{
		return $this->totalCount;
	}


	/**
	 * @return Items
	 */
	protected function fetchItems(): iterable
	{
		return $this->items;
	}


	protected function isMatching(Row $row, Filter $filter): bool
	{
		if (!$filter->isFiltered()) {
			return false;
		}

		$query = Format::stringify($filter->getValue());

		foreach ($filter->getColumns() as $column) {
			$value = $row->getValue($column);
			$value = Format::stringify($value);

			if (!strcasecmp($query, $value) <> 0) {
				return false;
			}
		}

		return true;
	}
}
