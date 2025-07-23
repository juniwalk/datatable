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
use JuniWalk\DataTable\Columns\NumberColumn;
use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Container;

/**
 * @phpstan-import-type ColumnName from Column
 */
trait Columns
{
	public function addColumnText(string $name, ?string $label): TextColumn
	{
		return $this->addColumn($name, new TextColumn($label));
	}


	public function addColumnNumber(string $name, ?string $label): NumberColumn
	{
		return $this->addColumn($name, new NumberColumn($label));
	}


	public function addColumnDate(string $name, ?string $label): DateColumn
	{
		return $this->addColumn($name, new DateColumn($label));
	}


	/**
	 * @param array<string, Action> $actions
	 */
	protected function addColumnAction(string $name, ?string $label, array $actions): ActionColumn
	{
		return $this->addColumn($name, new ActionColumn($label))->addActions($actions);
	}


	/**
	 * @template T of Column
	 * @param  T $column
	 * @return T
	 */
	public function addColumn(string $name, Column $column): Column
	{
		$this['columns']->addComponent($column, $name);
		return $column;
	}


	/**
	 * @return ($require is true ? Column : ?Column)
	 */
	public function getColumn(string $name, bool $require = true): ?Column
	{
		return $this['columns']->getComponent($name, $require);
	}


	/**
	 * @return array<ColumnName, Column>
	 */
	public function getColumns(): array
	{
		$columns = $this['columns']->getComponents(null, Column::class);

		/** @var array<ColumnName, Column> */
		return iterator_to_array($columns);
	}


	public function removeColumn(string $name): void
	{
		// todo: make sure this works properly as there was PHPStan issue
		$this['columns']->removeComponent($this->getColumn($name));
	}


	protected function createComponentColumns(): Container
	{
		return new Container;
	}
}
