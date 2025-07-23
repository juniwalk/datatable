<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Enums\Sort;
use Nette\ComponentModel\IComponent;

/**
 * @phpstan-type ColumnName non-empty-string
 */
interface Column extends IComponent
{
	public function isFiltered(): bool;
	public function addFilter(Filter $filter): self;

	/**
	 * @return array<string, Filter>
	 */
	public function getFilters(): array;

	public function setSortable(bool|string $sortable): self;
	public function isSortable(): ?bool;

	public function getSortedBy(): ?string;

	public function setSorted(?Sort $sort): self;
	public function isSorted(): ?Sort;


	public function setAlign(string $align): self;
	public function getAlign(): string;


	public function render(Row $row): void;
	public function renderLabel(): void;
}
