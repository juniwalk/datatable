<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

/**
 * @phpstan-type Item object|array<string, mixed>
 * @phpstan-type Items array<int|string, Item>
 */
interface Source
{
	public function setPrimaryKey(string $primaryKey): self;
	public function getPrimaryKey(): string;
	public function getCount(): int;

	/**
	 * @return Items
	 */
	public function fetchItems(Table $table): iterable;

	/**
	 * @return Items
	 */
	public function fetchItem(int|string $id): iterable;
}
