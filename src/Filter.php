<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;

/**
 * @phpstan-type ColumnName non-empty-string
 */
interface Filter extends IComponent
{
	/**
	 * @return array<string, mixed>
	 */
	public function getConditions(): array;

	public function setColumns(string ...$column): self;

	/**
	 * @return ColumnName[]
	 */
	public function getColumns(): array;

	public function setFiltered(bool $filtered): self;
	public function isFiltered(): bool;

	public function setValue(mixed $filter): self;
	public function getValue(): mixed;

	public function createInput(Form $form): void;
	public function render(Form $form): void;
}
