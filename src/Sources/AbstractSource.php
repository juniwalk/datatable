<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Source;

/**
 * @phpstan-import-type Items from Source
 */
abstract class AbstractSource implements Source
{
	/**
	 * @param array<string, Filter> $filters
	 * @param array<string, Column> $columns
	 * @return Items
	 */
	public function fetchItems(array $filters, array $columns, int $offset, int $limit): iterable
	{
		// ! first filter, then sort and then limit
		$this->filter($filters);
		$this->sort($columns);
		$this->limit($offset, $limit);

		return $this->getData();
	}


	/**
	 * @return Items
	 */
	public function fetchItem(int|string $id): iterable
	{
		$this->filterOne($id);

		return $this->getData();
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
	abstract protected function getData(): iterable;
}
