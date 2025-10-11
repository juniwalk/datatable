<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use Closure;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Hideable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Columns\Traits\Filters;
use JuniWalk\DataTable\Columns\Traits\Hiding;
use JuniWalk\DataTable\Columns\Traits\Sorting;
use JuniWalk\DataTable\Actions\DropdownAction;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Table;
use JuniWalk\DataTable\Tools\Option;
use JuniWalk\DataTable\Traits\LinkArguments;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;

class DropdownColumn extends AbstractColumn implements Sortable, Filterable, Hideable
{
	use Sorting, Filters, Hiding;
	use LinkArguments;

	protected Align $align = Align::Right;
	protected DropdownAction $dropdown;
	protected Closure $optionFactory;

	protected bool $isAjaxEnabled = false;
	protected bool $isDisabled = false;

	/** @var mixed[] */
	protected array $items = [];


	/**
	 * @param mixed[] $items
	 */
	public function setItems(array $items): static
	{
		$this->items = $items;
		return $this;
	}


	public function setOptionFactory(Closure $optionFactory): static
	{
		$this->optionFactory = $optionFactory;
		return $this;
	}


	public function setAjaxEnabled(bool $ajaxEnabled = true): static
	{
		$this->isAjaxEnabled = $ajaxEnabled;
		return $this;
	}


	public function setDisabled(bool $disabled = true): static
	{
		$this->isDisabled = $disabled;
		return $this;
	}


	public function render(Row $row): void
	{
		echo $this->formatValue($row);
	}


	protected function formatValue(Row $row): Html
	{
		if (!$item = $row->getValue($this)) {
			return Html::el();
		}

		$option = $this->createOption($item);

		if ($this->isDisabled) {
			return $option->createBadge();
		}

		$this->dropdown->setLabel($option->label)
			->setIcon($option->icon)
			->setAttribute('class', 'btn btn-xs')
			->addAttribute('class', $option->color?->for('btn'));

		foreach ($this->dropdown->getActions() as $action) {
			$action->removeAttribute('class', 'active');

			if ($this->dropdown->getLabel() === $action->getLabel()) {
				$action->addAttribute('class', 'active');
			}
		}

		return $this->dropdown->createButton($row);
	}


	protected function createActions(Table $table): void
	{
		$this->dropdown = $table->addActionDropdown($this->name, '');

		foreach ($this->items as $item) {
			$option = $this->createOption($item);
			$arguments = array_merge($this->args, [
				'value' => $option->value,
			]);

			$action = $this->dropdown->addActionLink((string) $option->value, $option->label)
				->setLink($this->dest ?? $this->name.'!', $arguments)
				// ->addAttribute('class', $option->color?->for('text'))
				->setIcon($option->icon);

			if ($this->isAjaxEnabled) {
				$action->addAttribute('class', 'ajax');
			}
		}

		$table->allowRowAction($this->name, fn() => false);
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function createOption(mixed $item): Option
	{
		if (!isset($this->optionFactory)) {
			throw InvalidStateException::callbackMissing($this, 'optionFactory');
		}

		return call_user_func($this->optionFactory, $item);
	}


	protected function validateParent(IContainer $parent): void
	{
		$this->monitor(Table::class, function(Table $table) {
			$table->when('render', fn() => $this->createActions($table));
		});

		parent::validateParent($parent);

		$this->onAnchor[] = function() {
			$this->lookup(Table::class);
		};
	}
}
