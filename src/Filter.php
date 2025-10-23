<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Closure;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Filters\Interfaces\FilterRange;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Form;

/**
 * @phpstan-type FilterStruct Filter&(FilterSingle|FilterRange|FilterList)
 */
interface Filter extends IComponent
{
	public function getLabel(): string;
	public function getType(): string;
	public function isFiltered(): bool;

	public function setField(?string $field): static;
	public function getField(): ?string;

	/**
	 * @return array<string, Column>
	 */
	public function getColumns(): array;
	public function setColumns(Column ...$column): static;
	public function hasColumn(string $columnName): bool;

	public function setCondition(?Closure $condition): static;
	public function hasCondition(): bool;
	public function applyCondition(mixed $model): bool;

	public function attachToForm(Form $form): void;
	public function firstInput(Form $form): IComponent;
	public function fieldName(): string;
}
