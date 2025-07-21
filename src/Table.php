<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Nette\Application\UI\Control;

class Table extends Control
{
	use Traits\Columns;
	use Traits\Sorting;
	use Traits\Filters;
	use Traits\Sources;


	// todo add perPage
	// todo add page


	public function render(): void
	{
		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/default.latte');

		if (!isset($this->source)) {
			throw new \Exception('No source set');
		}


		$this->source->filter($this->filter);
		$this->source->sort($this->sort);


		// todo: handle onDataLoaded event in the Source
		$items = $this->source->getItems();
		$columns = $this->getColumns();

		foreach ($columns as $name => $column) {
			$column->setFiltered((bool) ($this->filter[$name] ?? false));
			$column->setFilter($this->filter[$name] ?? null);
			$column->setSort($this->sort[$name] ?? null);
		}

		$template->add('columns', $columns);
		$template->add('items', $items);

		bdump($this);

		$template->render();
	}
}
