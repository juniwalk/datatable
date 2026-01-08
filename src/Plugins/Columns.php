<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use Closure;
use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Exclusive;
use JuniWalk\DataTable\Columns\Interfaces\Hideable;
use JuniWalk\DataTable\Columns\ActionColumn;
use JuniWalk\DataTable\Columns\DateColumn;
use JuniWalk\DataTable\Columns\DropdownColumn;
use JuniWalk\DataTable\Columns\EnumColumn;
use JuniWalk\DataTable\Columns\LinkColumn;
use JuniWalk\DataTable\Columns\NumberColumn;
use JuniWalk\DataTable\Columns\OrderColumn;
use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Enums\Option;
use JuniWalk\DataTable\Exceptions\ColumnAmbiguityException;
use JuniWalk\DataTable\Exceptions\ColumnNotFoundException;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Traits\LinkHandler;
use JuniWalk\Utils\Enums\Casing;
use JuniWalk\Utils\Strings;
use Nette\Application\UI\Template;

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

		$this->setOption(Option::StateColumns, $columnsHidden);

		$this->redrawControl('toolbar');
		$this->redrawControl('table');
		$this->redirect('this');
	}


	public function handleShowDefault(): void
	{
		$this->setOption(Option::StateColumns, null);

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

		if (!$column instanceof Hideable) {
			throw InvalidStateException::columnNotHideable($column);
		}

		if (!$columnName = $column->getName()) {
			throw InvalidStateException::notAttached($column);
		}

		/** @var array<string, bool> */
		$columnsHidden = $this->getOption(Option::StateColumns, []);
		$isHidden = $columnsHidden[$columnName] ?? $column->isDefaultHide();

		$columnsHidden[$columnName] = !$isHidden;

		$this->setOption(Option::StateColumns, $columnsHidden);

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


	public function addColumnOrder(string $name, string $label): OrderColumn
	{
		return $this->addColumn($name, new OrderColumn($label));
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
	 * @throws ColumnAmbiguityException
	 */
	public function addColumn(string $name, Column $column): Column
	{
		if ($column instanceof Exclusive && $related = $this->getColumnByType($column::class, false)) {
			throw ColumnAmbiguityException::fromColumn($related, $name);
		}

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
	 * @template T of Column
	 * @param  class-string<T> $class
	 * @return ($require is true ? T : ?T)
	 * @throws ColumnAmbiguityException
	 * @throws ColumnNotFoundException
	 */
	public function getColumnByType(string $class, bool $require = true): ?Column
	{
		$columns = array_filter($this->columns, fn($x) => is_a($x, $class));

		if ($require && empty($columns)) {
			throw ColumnNotFoundException::fromClass($class);
		}

		if (sizeof($columns) > 1) {
			throw ColumnAmbiguityException::fromColumns($columns);
		}

		return array_values($columns)[0] ?? null;
	}


	/**
	 * @return array<string, Column>
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}


	/**
	 * ? Modification through callback to ignore PHPStan issues with different interfaces on Column
	 * @throws ColumnNotFoundException
	 */
	public function modifyColumn(string $name, Closure $modify): static
	{
		$modify($this->getColumn($name));
		return $this;
	}


	/**
	 * @throws ColumnNotFoundException
	 */
	public function removeColumn(string $name): void
	{
		$this->getColumn($name)->setParent(null);
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


	/**
	 * @throws ColumnNotFoundException
	 */
	public function isColumnHideable(Column|string $column): bool
	{
		if (!$this->isColumnsHideable) {
			return false;
		}

		if (!$column instanceof Column) {
			$column = $this->getColumn($column);
		}

		return $column instanceof Hideable;
	}


	protected function onRenderColumns(Template $template): void
	{
		if ($actions = $this->getActions()) {
			$this->addColumnAction('__actions', 'datatable.column.action', $actions);
		}

		$template->attributes['data-dt-sticky-header'] = true;
		$template->columns = $this->columns;

		if (!$this->isColumnsHideable) {
			return;
		}

		$dropdown = $this->addToolbarDropdown('__column_toggle', '', '__columns')
			->setIcon('fa-eye')->setClass('btn btn-sm btn-secondary');

		$dropdown->addActionLink('__column_show_all', 'datatable.column.show-all')
			->setIcon('fa-eye')->addClass('ajax')
			->setLink('showAll!');

		$dropdown->addActionLink('__column_show_default', 'datatable.column.show-default')
			->setIcon('fa-redo')->addClass('ajax')
			->setLink('showDefault!');

		$dropdown->addDivider();

		/** @var array<string, bool> */
		$columnsHidden = $this->getOption(Option::StateColumns, []);

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
