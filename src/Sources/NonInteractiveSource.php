<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use JuniWalk\DataTable\Source;

class NonInteractiveSource implements Source
{
	public static function isModel(mixed $model): bool
	{
		return true;
	}


	public function setPrimaryKey(string $primaryKey): static
	{
		return $this;
	}


	public function getPrimaryKey(): string
	{
		return '';
	}


	public function getCountOnPage(): int
	{
		return 0;
	}


	public function getCount(): ?int
	{
		return null;
	}


	public function setIndeterminate(bool $isIndeterminate = true): static
	{
		return $this;
	}


	public function isIndeterminate(): bool
	{
		return false;
	}


	public function fetchItems(array $filters, array $sorting, int $offset, int $limit): array
	{
		return [];
	}


	public function fetchItem(int|string ...$id): array
	{
		return [];
	}


	public function clear(): void
	{
	}
}
