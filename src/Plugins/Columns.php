<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\ActionColumn;
use JuniWalk\DataTable\Columns\DateColumn;
use JuniWalk\DataTable\Columns\EnumColumn;
use JuniWalk\DataTable\Columns\LinkColumn;
use JuniWalk\DataTable\Columns\NumberColumn;
use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Container;
use JuniWalk\DataTable\Enums\Storage;
use JuniWalk\DataTable\Traits\LinkHandler;

/**
 * @phpstan-import-type LinkArgs from LinkHandler
 */
trait Columns
{
	public function addColumnText(string $name, string $label): TextColumn
	{
		return $this->addColumn($name, new TextColumn($label));
	}


	/**
	 * @param LinkArgs $args
	 */
	public function addColumnLink(string $name, string $label, string $dest = '', array $args = []): LinkColumn
	{
		return $this->addColumn($name, new LinkColumn($label))->setLink($dest, $args);
	}


	public function addColumnEnum(string $name, string $label): EnumColumn
	{
		return $this->addColumn($name, new EnumColumn($label));
	}


	public function addColumnNumber(string $name, string $label): NumberColumn
	{
		return $this->addColumn($name, new NumberColumn($label));
	}


	public function addColumnDate(string $name, string $label): DateColumn
	{
		return $this->addColumn($name, new DateColumn($label));
	}


	/**
	 * @param array<string, Action> $actions
	 */
	protected function addColumnAction(string $name, string $label, array $actions): ActionColumn
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
		/** @var T */
		return $this->__columns()->add($name, $column);
	}


	/**
	 * @return ($require is true ? Column : ?Column)
	 */
	public function getColumn(string $name, bool $require = true): ?Column
	{
		return $this->__columns()->get($name, $require);
	}


	/**
	 * @return array<string, Column>
	 */
	public function getColumns(): array
	{
		return $this->__columns()->list();
	}


	public function removeColumn(string $name): void
	{
		$this->__columns()->remove($name);
	}


	/**
	 * @return Container<Column>
	 */
	private function __columns(): Container
	{
		return $this->getComponent(Storage::Columns->value);
	}
}
