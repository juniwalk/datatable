<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Exceptions\SourceMissingException;
use JuniWalk\DataTable\Traits\Translation;
use JuniWalk\Utils\Interfaces\EventHandler;
use JuniWalk\Utils\Traits\Events;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use stdClass;
use Stringable;

class Table extends Control implements EventHandler
{
	use Events, Translation;

	use Plugins\Session;
	use Plugins\Sources;
	use Plugins\Columns;
	use Plugins\Filters;
	use Plugins\Actions;
	use Plugins\Toolbar;
	use Plugins\Sorting;
	use Plugins\Pagination;

	protected Stringable|string|null $caption = null;


	public function setCaption(Stringable|string|null $caption): static
	{
		$this->caption = $caption;
		return $this;
	}


	public function getCaption(): Stringable|string|null
	{
		return $this->caption;
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public function loadState(array $params): void
	{
		$params = $this->loadStateSorting($params);
		$params = $this->loadStateFilters($params);

		parent::loadState($params);
	}


	/**
	 * @param array<string, mixed> $params
	 * @param-out array<string, mixed> $params
	 */
	public function saveState(array &$params): void
	{
		$params = $this->saveStateSorting($params);
		$params = $this->saveStateFilters($params);

		parent::saveState($params);	// @phpstan-ignore paramOut.type (No control over this)
	}


	public function flashMessage(Stringable|stdClass|string $message, string $type = 'info'): stdClass
	{
		return $this->getPresenter()->flashMessage($message, $type);
	}


	/**
	 * @throws SourceMissingException
	 */
	public function render(): void
	{
		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/table.latte');
		$template->add('controlName', $this->getUniqueId());

		if ($actions = $this->getActions()) {
			$this->addColumnAction('__actions', 'datatable.column.action', $actions);
		}

		$toolbar = $this->getToolbarActionsGrouped();
		$columns = $this->getColumns();
		$filters = $this->getFilters();

		// todo: add some argumens like $template
		$this->trigger('render');

		$source = $this->getSource();
		$items = !isset($this->redrawItem)
			? $source->fetchItems($filters, $columns, $this->getOffset(), $this->getCurrentLimit())
			: $source->fetchItem($this->redrawItem);

		$rows =  [];

		$this->trigger('load', $items);

		foreach ($items as $item) {
			$rows[] = $row = new Row($item, $source);
			$this->trigger('item', $item, $row);
		}

		// bdump($this);

		$template->add('rows', $rows);
		$template->add('toolbar', $toolbar);
		$template->add('columns', $columns);
		$template->add('filters', $filters);
		$template->add('caption', $this->caption);

		$template->render();
	}


	protected function validateParent(IContainer $parent): void
	{
		$this->watch('render');
		$this->watch('load');
		$this->watch('item');

		// ? Parent has to be validated first so the loadState is called
		parent::validateParent($parent);

		$this->monitor(Presenter::class, fn() => $this->validateSession());
		$this->monitor(Presenter::class, fn() => $this->validateSources());
		$this->monitor(Presenter::class, fn() => $this->validateFilters());
		$this->monitor(Presenter::class, fn() => $this->validateSorting());
	}
}
