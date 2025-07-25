<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Enums\Sort;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;

/**
 * @phpstan-type State array{
 * 		sort: array<string, value-of<Sort>>,
 * 		filter: array<string, scalar|scalar[]>,
 * }
 */
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
	 * @param State $params
	 */
	public function loadState(array $params): void
	{
		$limit = $params['limit'] ?? null;

		if (!$limit || !in_array($limit, $this->perPage)) {
			$this->limit = null;
		}

		foreach ($params['sort'] ?? [] as $column => $order) {
			$params['sort'][$column] = Sort::make($order, false);
		}

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

		if (!isset($this->source)) {
			throw new \Exception('No source set');
		}

		if ($actions = $this->getActions()) {
			$this->addColumnAction('actions', 'Akce', $actions);
		}


		$sort = $this->getCurrentSort();
		$columns = $this->getColumns();
		$filters = $this->getFilters();


		foreach ($filters as $name => $filter) {
			$filter->setFiltered((bool) ($this->filter[$name] ?? false));
			$filter->setValue($this->filter[$name] ?? null);

			foreach ($filter->getColumns() as $column) {
				// todo: make sure $columns[$column] exists

				if (!$columns[$column] instanceof Filterable) {
					continue;
				}

				$columns[$column]->addFilter($filter);
			}
		}


		foreach ($columns as $name => $column) {
			if (!$column instanceof Sortable) {
				continue;
			}

			$column->setSorted($sort[$name] ?? null);

			// ? Set column as sortable only if there is no override
			if ($this->isSortable() && $column->isSortable() === null) {
				$column->setSortable(true);
			}
		}

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
