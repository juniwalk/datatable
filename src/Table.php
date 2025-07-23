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
		$template->setFile(__DIR__.'/templates/default.latte');

		if (!isset($this->source)) {
			throw new \Exception('No source set');
		}

		if ($actions = $this->getActions()) {
			$this->addColumnAction('actions', 'Akce', $actions);
		}


		$columns = $this->getColumns();
		$sort = $this->getCurrentSort();

		foreach ($columns as $name => $column) {
			$column->setSorted($sort[$name] ?? null);

			// ? Set column as sortable only if there is no override
			if ($this->isSortable() && $column->isSortable() === null) {
				$column->setSortable(true);
			}


			// todo: improve filter handling
			$column->setFiltered((bool) ($this->filter[$name] ?? false));
			$column->setFilter($this->filter[$name] ?? null);
		}

		$limit = $this->limit ?? $this->limitDefault ?? $this->perPage[0] ?? 10;

		// todo: first filter, then sort and then limit
		$this->source->filter($this->filter);
		$this->source->sort($sort);
		$this->source->limit($this->page, $limit);

		$rows = [];

		foreach ($this->source->getItems() as $key => $item) {
			$rows[$key] = new Row($key, $item);
		}

		$template->add('columns', $columns);
		$template->add('rows', $rows);

		$template->add('page', $this->page);
		$template->add('limit', $limit);
		$template->add('perPage', $this->perPage);

		// bdump($this);

		$template->render();
	}
}
