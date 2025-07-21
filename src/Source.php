<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Enums\Sort;

/**
 * @phpstan-type Item array<string, mixed>
 * @phpstan-type Items array<int|string, Item>
 */
interface Source
{
	/**
	 * @return Items
	 */
	public function getItems(): iterable;
	public function getCount(): int;

	/**
	 * @param array<non-empty-string, scalar> $filter
	 */
	public function filter(array $filter): void;

	/**
	 * @param array<non-empty-string, Sort|'asc'|'desc'|null> $sort
	 */
	public function sort(array $sort): void;
}
