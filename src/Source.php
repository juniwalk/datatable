<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\Utils\Interfaces\EventHandler;

/**
 * @phpstan-type Item object|array<string, mixed>
 * @phpstan-type Items array<int|string, Item>
 */
interface Source extends EventHandler
{
	public function setPrimaryKey(string $primaryKey): self;
	public function getPrimaryKey(): string;

	/**
	 * @param array<string, Filter> $filters
	 */
	public function filter(array $filters): void;
	public function filterById(int|string ...$rows): void;

	/**
	 * @param array<string, Column> $columns
	 */
	public function sort(array $columns): void;
	public function limit(int $offset, int $limit): void;

	/**
	 * @return Row[]
	 */
	public function fetchRows(): iterable;
	public function totalCount(): int;
}
