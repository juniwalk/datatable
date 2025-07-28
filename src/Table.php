<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;

class Table extends Control
{
	use Traits\Actions;
	use Traits\Columns;
	use Traits\Filters;
	use Traits\Sorting;
	use Traits\Sources;
	use Traits\Pagination;


	// todo: implement optional state store / restore from session


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


	public function render(): void
	{
		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/table.latte');
		$template->add('controlName', $this->getUniqueId());

		if (!$source = $this->getSource()) {
			// todo: throw SourceMissingException
			throw new \Exception('No source set');
		}

		if ($actions = $this->getActions()) {
			$this->addColumnAction('actions', 'Akce', $actions);
		}

		$columns = $this->getColumns();
		$filters = $this->getFilters();

		// todo: first filter, then sort and then limit
		// $source->filterById($listOfRowsToRedraw);	// ? choose which method to call
		$source->filter($filters);

		$source->sort($columns);
		$source->limit($this->page, $this->getCurrentLimit());

		$rows = [];

		foreach ($source->fetchItems() as $item) {
			$row = new Row($item, $source);
			$rows[$row->getId()] = $row;
		}

		$template->add('columns', $columns);
		$template->add('filters', $filters);
		$template->add('rows', $rows);

		// bdump($this);

		$template->render();
	}


	protected function validateParent(IContainer $parent): void
	{
		// ? Parent has to be validated first so the loadState is called
		parent::validateParent($parent);

		$this->monitor(Presenter::class, fn() => $this->validateSorting());
		$this->monitor(Presenter::class, fn() => $this->validateFilters());
		$this->monitor(Presenter::class, fn() => $this->validateSources());
	}


	protected function createComponent(string $name): ?IComponent
	{
		static $containers = [
			Container::Actions,
			Container::Columns,
			Container::Filters,
		];

		if (in_array($name, $containers)) {
			return new Container;
		}

		return parent::createComponent($name);
	}
}
