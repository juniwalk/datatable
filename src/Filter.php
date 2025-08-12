<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Closure;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;

interface Filter extends IComponent
{
	/**
	 * @return array<string, Column>
	 */
	public function getColumns(): array;
	public function setColumns(Column ...$column): self;
	public function hasColumn(string $columnName): bool;

	public function setCondition(?Closure $condition): self;
	public function hasCondition(): bool;
	public function applyCondition(mixed $model): bool;

	public function setValue(mixed $filter): self;
	public function getValue(): mixed;

	public function isFiltered(): bool;

	public function attachToForm(Form $form): void;
	public function format(mixed $value): ?string;

	public function render(Form $form): void;
}
