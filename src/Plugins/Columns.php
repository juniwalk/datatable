<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Hideable;
use JuniWalk\DataTable\Columns\ActionColumn;
use JuniWalk\DataTable\Columns\DateColumn;
use JuniWalk\DataTable\Columns\DropdownColumn;
use JuniWalk\DataTable\Columns\EnumColumn;
use JuniWalk\DataTable\Columns\LinkColumn;
use JuniWalk\DataTable\Columns\NumberColumn;
use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Enums\Option;
use JuniWalk\DataTable\Exceptions\ColumnNotFoundException;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Traits\LinkHandler;
use JuniWalk\Utils\Enums\Casing;
use JuniWalk\Utils\Strings;

/**
 * @phpstan-import-type LinkArgs from LinkHandler
 */
trait Columns
{
	protected bool $isColumnsHideable = false;

	/** @var array<string, Column> */
	protected array $columns = [];


	public function handleShowAll(): void
	{
		$columnsHidden = array_filter($this->columns, fn($x) => $x instanceof Hideable);
		$columnsHidden = array_map(fn() => false, $columnsHidden);

		$this->setOption(Option::HiddenColumns, $columnsHidden);

		$this->redrawControl('toolbar');
		$this->redrawControl('table');
		$this->redirect('this');
	}


	public function handleShowDefault(): void
	{
		$this->setOption(Option::HiddenColumns, []);

		$this->redrawControl('toolbar');
		$this->redrawControl('table');
		$this->redirect('this');
	}


	/**
	 * @throws InvalidStateException
	 */
	public function handleShowToggle(string $column): void
	{
		$column = $this->getColumn($column);
		$name = $column->getName();

		if (!$column instanceof Hideable) {
			throw InvalidStateException::columnNotHideable($column);
		}

		/** @var array<string, bool> */
		$columnsHidden = $this->getOption(Option::HiddenColumns, []);
		$isHidden = $columnsHidden[$name] ?? $column->isDefaultHide();

		$columnsHidden[$name] = !$isHidden;

		$this->setOption(Option::HiddenColumns, $columnsHidden);

		$this->redrawControl('toolbar');
		$this->redrawControl('table');
		$this->redirect('this');
	}


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
	 * @param mixed[] $items
	 */
	public function addColumnDropdown(string $name, string $label, array $items): DropdownColumn
	{
		return $this->addColumn($name, new DropdownColumn($label))->setItems($items);
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
		$column->setParent($this, $name);

		$this->columns[$name] = $column;
		return $column;
	}


	/**
	 * @return ($require is true ? Column : ?Column)
	 * @throws ColumnNotFoundException
	 */
	public function getColumn(string $name, bool $require = true): ?Column
	{
		if ($require && !isset($this->columns[$name])) {
			throw ColumnNotFoundException::fromName($name);
		}

		return $this->columns[$name] ?? null;
	}


	/**
	 * @return array<string, Column>
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}


	public function removeColumn(string $name): void
	{
		$this->getColumn($name, false)?->setParent(null);
		unset($this->columns[$name]);
	}


	public function setColumnsHideable(bool $columnsHideable = true): static
	{
		$this->isColumnsHideable = $columnsHideable;
		return $this;
	}


	public function isColumnsHideable(): bool
	{
		return $this->isColumnsHideable;
	}


	public function isColumnHideable(Column $column): bool
	{
		if (!$this->isColumnsHideable) {
			return false;
		}

		return $column instanceof Hideable;
	}


	protected function validateColumns(): void
	{
		if (!$this->isColumnsHideable) {
			return;
		}

		$dropdown = $this->addToolbarDropdown('__column_toggle', '', '__columns')
			->setIcon('fa-cog')->setClass('btn btn-sm btn-secondary');

		$dropdown->addActionLink('__column_show_all', 'datatable.column.show-all')
			->setIcon('fa-eye')->addClass('ajax')
			->setLink('showAll!');

		$dropdown->addActionLink('__column_show_default', 'datatable.column.show-default')
			->setIcon('fa-redo')->addClass('ajax')
			->setLink('showDefault!');

		$dropdown->addDivider();

		/** @var array<string, bool> */
		$columnsHidden = $this->getOption(Option::HiddenColumns, []);

		foreach ($this->columns as $name => $column) {
			if (!$column instanceof Hideable) {
				continue;
			}

			$actionName = '__column_toggle_'.Casing::Camel->format(Strings::webalize($name));
			$action = $dropdown->addActionLink($actionName, $column->getLabel())
				->setIcon('fa-square-check')->addClass('ajax')
				->setLink('showToggle!', ['column' => $name]);

			$column->setHidden($columnsHidden[$name] ?? null);

			if ($column->isHidden()) {
				$action->addClass('text-secondary');
				$action->setIcon('far fa-square');
			}
		}
	}
}
