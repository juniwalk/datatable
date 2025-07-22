<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Enums\Sort;
use Nette\Application\UI\Control;

/**
 * @phpstan-import-type ColumnName from Column
 * @phpstan-type State array{
 * 		sort: array<ColumnName, 'asc'|'desc'>,
 * 		filter: array<ColumnName, scalar|scalar[]>,
 * }
 */
class Table extends Control
{
	use Traits\Actions;
	use Traits\Columns;
	use Traits\Filters;
	use Traits\Sorting;
	use Traits\Sources;


	// todo add perPage
	// todo add page


	// todo: implement optional state store / restore from session


	/**
	 * @param State $params
	 */
	public function loadState(array $params): void
	{
		foreach ($params['sort'] ?? [] as $column => $order) {
			$params['sort'][$column] = Sort::make($order, false);
		}

		parent::loadState($params);
	}


	public function render(): void
	{
		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/default.latte');

		if (!isset($this->source)) {
			throw new \Exception('No source set');
		}

		if ($actions = $this->getActions()) {
			$this->addColumnAction('actions', 'Akce', $actions);
		}


		$columns = $this->getColumns();

		foreach ($columns as $name => $column) {
			// ? Set column as sortable only if there is no override
			if ($this->isSortable() && $column->isSortable() === null) {
				$column->setSortable(true);
			}

			$column->setSorted($this->sort[$name] ?? null);



			// todo: improve filter handling
			$column->setFiltered((bool) ($this->filter[$name] ?? false));
			$column->setFilter($this->filter[$name] ?? null);
		}


		$this->source->filter($this->filter);
		$this->source->sort($this->sort);

		$rows = [];

		foreach ($this->source->getItems() as $key => $item) {
			$rows[$key] = new Row($key, $item);
		}

		$template->add('columns', $columns);
		$template->add('rows', $rows);

		// bdump($this);

		$template->render();
	}
}
