<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Enums\Sort;
use Nette\ComponentModel\IComponent;

interface Column extends IComponent
{
	public function setSort(Sort|string|null $sort): self;
	public function setSortable(bool|string $sortable): self;
	public function isSortable(): bool;


	public function setFilter(mixed $filter): self;
	public function setFiltered(bool $filtered): self;
	public function isFiltered(): bool;


	public function setAlign(string $align): self;
	public function getAlign(): string;


	public function renderLabel(): void;
	public function render(mixed $row): void;
}
