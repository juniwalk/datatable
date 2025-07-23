<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

/**
 * @phpstan-import-type ColumnName from Column
 * @phpstan-type Item object|array<string, mixed>
 * @phpstan-type Items array<int|string, Item>
 */
interface Source
{
	public function setPrimaryKey(string $primaryKey): self;
	public function getPrimaryKey(): ?string;

	/**
	 * @return Items
	 */
	public function fetchItems(): iterable;
	public function totalCount(): int;

	/**
	 * @param array<string, Filter> $filters
	 */
	public function filter(array $filters): void;
	public function filterById(int|string ...$rows): void;

	/**
	 * @param array<ColumnName, Column> $columns
	 */
	public function sort(array $columns): void;


	public function limit(int $page, int $limit): void;
}
