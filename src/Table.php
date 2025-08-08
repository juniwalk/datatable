<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Enums\Storage;
use JuniWalk\DataTable\Exceptions\SourceMissingException;
use JuniWalk\Utils\Interfaces\EventHandler;
use JuniWalk\Utils\Traits\Events;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;

class Table extends Control implements EventHandler
{
	use Events;

	use Plugins\Actions;
	use Plugins\Toolbar;
	use Plugins\Columns;
	use Plugins\Filters;
	use Plugins\Sorting;
	use Plugins\Sources;
	use Plugins\Session;
	use Plugins\Pagination;


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


	public function flashMessage(string|\stdClass|\Stringable $message, string $type = 'info'): \stdClass
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
			$this->addColumnAction('actions', 'Akce', $actions);
		}

		$toolbar = $this->getToolbarActionsGrouped();
		$columns = $this->getColumns();
		$filters = $this->getFilters();

		// todo: add some argumens like $template
		$this->trigger('render');

		// todo: move this into Sources plugin [?]
		$source = $this->getSource();
		// ! first filter, then sort and then limit
		$source->filter($filters);	// $source->filterById($listOfRowsToRedraw);	// ? choose which method to call
		$source->sort($columns);
		$source->limit($this->page, $this->getCurrentLimit());

		// bdump($this);

		$template->add('rows', $source->fetchRows());
		$template->add('toolbar', $toolbar);
		$template->add('columns', $columns);
		$template->add('filters', $filters);

		$template->render();
	}


	protected function validateParent(IContainer $parent): void
	{
		$this->watch('render');

		// ? Parent has to be validated first so the loadState is called
		parent::validateParent($parent);

		$this->monitor(Presenter::class, fn() => $this->validateSession());
		$this->monitor(Presenter::class, fn() => $this->validateSources());
		$this->monitor(Presenter::class, fn() => $this->validateFilters());
		$this->monitor(Presenter::class, fn() => $this->validateSorting());
	}


	protected function createComponent(string $name): ?IComponent
	{
		if (Storage::tryFrom($name)) {
			return new Container;
		}

		return parent::createComponent($name);
	}
}
