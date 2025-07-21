<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\ActionColumn;
use JuniWalk\DataTable\Columns\DateColumn;
use JuniWalk\DataTable\Columns\TextColumn;

trait Columns
{
	// todo: store columns in subcomponent so there is no name clashing with actions / filters


	public function addTextColumn(string $name, ?string $label): TextColumn
	{
		$this->addComponent($column = new TextColumn($label), $name);
		return $column;
	}


	public function addDateColumn(string $name, ?string $label): DateColumn
	{
		$this->addComponent($column = new DateColumn($label), $name);
		return $column;
	}


	/**
	 * @param iterable<string, Action> $actions
	 */
	protected function addColumnAction(string $name, ?string $label, iterable $actions): ActionColumn
	{
		$this->addComponent($column = new ActionColumn($label), $name);
		$column->addActions($actions);
		return $column;
	}


	/**
	 * @return ($require is true ? Column : ?Column)
	 */
	public function getColumn(string $name, bool $require = true): ?Column
	{
		return $this->getComponent($name, $require);
	}


	/**
	 * @return iterable<string, Column>
	 */
	public function getColumns(): iterable
	{
		return $this->getComponents(null, Column::class);	// @phpstan-ignore return.type (List is filtered for Column::class)
	}


	public function removeColumn(string $name): void
	{
		// todo: make sure this works properly as there was PHPStan issue
		$this->removeComponent($this->getColumn($name));
	}
}
