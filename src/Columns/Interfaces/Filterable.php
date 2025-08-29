<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns\Interfaces;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Filter;

interface Filterable extends Column
{
	public function isFiltered(): bool;

	public function addFilter(Filter $filter): static;

	/**
	 * @return array<string, Filter>
	 */
	public function getFilters(): array;

	public function detectFilteredStatus(): void;
}
