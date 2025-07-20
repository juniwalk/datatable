<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\Utils\Arrays;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Control;

class Table extends Control
{
	use Traits\Columns;
	use Traits\Sorting;
	use Traits\Filters;

	private Source $source;

	// todo add perPage
	// todo add page


	// todo: implement optional state store / restore from session



	// todo: allow dynamic source creation from given data type (in different method)
	public function setSource(Source $source): void
	{
		$this->source = $source;
	}


	public function render(): void
	{
		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/default.latte');

		$columns = $this->getColumns();

		// foreach ($columns as $name => $column) {
		// 	// todo: check is sorted
		// 	// todo: check is filtered
		// }

		/** @var array<non-empty-string, Sort> */
		$sort = Arrays::map($this->sort, fn($x) => Sort::from($x));
		$filter = $this->filter; // todo: make list of filter instances

		// todo: do this in more elegant way
		foreach ($filter as $column => $query) {
			$this->getColumn($column, false)?->setFiltered(true);
		}

		$template->add('columns', $columns);
		$template->add('sorts', $sort);

		$this->source->filter($filter);
		$this->source->sort($sort);

		// todo: handle onDataLoaded event in the Source
		$template->add('items', $this->source->getItems());

		// bdump($this);

		$template->render();
	}
}
