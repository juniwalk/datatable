<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\DateColumn;
use JuniWalk\DataTable\Columns\TextColumn;

trait Columns
{
	// todo: store columns in subcomponent so there is no name clashing with actions / filters


	public function addTextColumn(string $name, ?string $label = null): TextColumn
	{
		$this->addComponent($column = new TextColumn($label), $name);
		return $column;
	}


	public function addDateColumn(string $name, ?string $label = null): DateColumn
	{
		$this->addComponent($column = new DateColumn($label), $name);
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
	 * @return Column[]
	 */
	public function getColumns(): array
	{
		return $this->getComponents(null, Column::class);
	}


	public function removeColumn(string $name): void
	{
		$this->removeComponent($name);
	}
}
