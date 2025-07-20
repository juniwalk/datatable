<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

interface Column
{
	public function setSortable(bool|string $sortable): self;
	public function isSortable(): bool;


	public function setFiltered(bool $filtered): self;
	public function isFiltered(): bool;


	public function setAlign(string $align): self;
	public function getAlign(): string;


	public function renderLabel(): void;
	public function render(mixed $row): void;
}
