<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

/**
 * @phpstan-import-type ColumnName from Column
 * @phpstan-type Item array<string, mixed>
 * @phpstan-type Items array<int|string, Item>
 */
interface Source
{
	/**
	 * @return Items
	 */
	public function fetchItems(): iterable;
	public function totalCount(): int;

	/**
	 * @param array<ColumnName, scalar> $filters
	 */
	public function filter(array $filters): void;
	public function filterOne(int|string ...$rows): void;

	/**
	 * @param array<ColumnName, Column> $columns
	 */
	public function sort(array $columns): void;


	public function limit(int $page, int $limit): void;
}
