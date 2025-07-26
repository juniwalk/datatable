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
	 * @param array<string, scalar|scalar[]> $params
	 */
	public function loadState(array $params): void
	{
		$params = $this->sortingPrepare($params);
		$params = $this->filtersPrepare($params);

		parent::loadState($params);
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

		if ($actions = $this->getActions()) {
			$this->addColumnAction('actions', 'Akce', $actions);
		}

		$columns = $this->getColumns();
		$filters = $this->getFilters();

		// todo: first filter, then sort and then limit
		$this->source->filter($filters);
		$this->source->sort($columns);
		$this->source->limit($this->page, $this->getCurrentLimit());

		$rows = [];

		foreach ($this->source->fetchItems() as $item) {
			$row = new Row($item, $this->primaryKey);
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

		$this->monitor(Presenter::class, fn() => $this->sortingProcess());
		$this->monitor(Presenter::class, fn() => $this->filtersProcess());
		$this->monitor(Presenter::class, fn() => $this->sourcesProcess());
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
