<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters\Interfaces;

use JuniWalk\DataTable\Interfaces\SearchProvider;

interface FilterSearchable
{
	public function setSearchProvider(?SearchProvider $provider, ?int $perPage = null): static;
	public function getSearchProvider(): ?SearchProvider;
	public function hasSearchProvider(): bool;
}
