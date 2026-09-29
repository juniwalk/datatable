<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Interfaces;

use Closure;
use JuniWalk\Form\SearchPayload;

interface CallbackSearchable
{
	public function setSearch(?Closure $renderer, ?int $maxResults = null): static;
	public function getSearch(): ?Closure;
	public function hasSearch(): bool;

	/**
	 * @return array<string, mixed[]>
	 */
	public function searchCallback(string $query, SearchPayload $payload): ?array;
}
