<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;

interface Filter extends IComponent
{
	/**
	 * @return string[]
	 */
	public function getColumns(): array;
	public function setColumns(string ...$column): self;

	public function setValue(mixed $filter): self;
	public function getValue(): mixed;

	public function isFiltered(): bool;

	public function createInput(Form $form): void;
	public function render(Form $form): void;
}
