<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Source;
use JuniWalk\DataTable\Table;

/**
 * @phpstan-import-type Items from Source
 */
abstract class AbstractSource implements Source
{
	protected string $primaryKey = 'id';

	protected ?int $count = null;
	protected int $countOnPage;


	public function setPrimaryKey(string $primaryKey): self
	{
		$this->primaryKey = $primaryKey;
		return $this;
	}


	public function getPrimaryKey(): string
	{
		return $this->primaryKey;
	}


	public function getCount(): ?int
	{
		return $this->count;
	}


	public function getCountOnPage(): int
	{
		return $this->countOnPage ?? 0;
	}


	/**
	 * @return Items
	 */
	public function fetchItems(Table $table): iterable
	{
		$columns = [];

		foreach ($table->getCurrentSort() as $name => $sort) {
			$columns[$name] = $table->getColumn($name, false);
		}

		// ! first filter, then sort and then limit
		$this->filter($table->getFilters());
		$this->sort(array_filter($columns));
		$this->limit($table->getOffset(), $table->getCurrentLimit());

		$items = $this->fetchData();

		$this->countOnPage = sizeof($items);

		return $items;
	}


	/**
	 * @return Items
	 */
	public function fetchItem(int|string $id): iterable
	{
		$this->filterOne($id);

		return $this->fetchData();
	}


	/**
	 * @param array<string, Filter> $filters
	 */
	abstract protected function filter(array $filters): void;
	abstract protected function filterOne(int|string $id): void;


	/**
	 * @param array<string, Column> $columns
	 */
	abstract protected function sort(array $columns): void;
	abstract protected function limit(int $offset, int $limit): void;


	/**
	 * @return Items
	 */
	abstract protected function fetchData(): array;
}
