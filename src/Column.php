<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Interfaces\Attributable;
use Nette\ComponentModel\IComponent;

interface Column extends IComponent, Attributable
{
	public function setLabel(string $label): static;
	public function getLabel(): string;

	public function setField(?string $field): static;
	public function getField(): ?string;

	/**
	 * @param value-of<Align> $align
	 */
	public function setAlign(Align|string $align): static;
	public function getAlign(): Align;

	public function isFiltered(): bool;
	public function isSortable(): ?bool;
	public function isHidden(): bool;

	public function render(Row $row): void;
	public function renderLabel(): void;
}
