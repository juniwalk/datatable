<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Enums\Align;
use Nette\ComponentModel\IComponent;

interface Column extends IComponent
{
	/**
	 * @param value-of<Align> $align
	 */
	public function setAlign(Align|string $align): self;
	public function getAlign(): Align;

	public function isFiltered(): bool;
	public function isSortable(): ?bool;

	public function render(Row $row): void;
	public function renderValue(Row $row): void;
	public function renderLabel(): void;
}
