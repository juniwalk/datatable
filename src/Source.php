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
	public function setPrimaryKey(string $primaryKey): static;
	public function getPrimaryKey(): string;

	public function getCount(): ?int;
	public function getCountOnPage(): int;

	public function setIndeterminate(bool $isIndeterminate = true): static;
	public function isIndeterminate(): bool;

	/**
	 * @param  Filter[] $filters
	 * @param  Column[] $sorting
	 * @return Items
	 */
	public function fetchItems(array $filters, array $sorting, int $offset, int $limit): array;

	/**
	 * @return Items
	 */
	public function fetchItem(int|string $id): array;
}
