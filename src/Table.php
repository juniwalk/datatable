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


	private Source $source;


	#[Persistent]
	/** @var array<string, 'asc'|'desc'> */
	public array $sort = [];
	private bool $isSortMultiple = false;


	#[Persistent]
	/** @var array<string, mixed> */
	public array $filter = [];


	// todo add perPage
	// todo add page


	// todo: implement optional state store / restore from session


	public function handleSort(string $column): void
	{
		if (!$this->getColumn($column, false)) {
			throw new \Exception;
		}

		$sort = $this->sort[$column] ?? null;
		$sort = Sort::make($sort, false);

		if (!$this->isSortMultiple) {
			$this->sort = [];
		}

		$this->sort[$column] = match ($sort) {
			Sort::ASC	=> Sort::DESC,
			Sort::DESC	=> null,
			null		=> Sort::ASC,
		};

		$this->redirect('this');
	}


	// todo: allow setting whole datatable as sortable


	public function setSortMultiple(bool $sortMultiple = true): void
	{
		$this->isSortMultiple = $sortMultiple;
	}


	public function isSortMultiple(): bool
	{
		return $this->isSortMultiple;
	}




	public function handleClear(string $column): void
	{
		unset($this->filter[$column]);

		$this->redirect('this');
	}

	public function handleClearAll(): void
	{
		$this->filter = [];

		$this->redirect('this');
	}



	// todo: allow dynamic source creation from given data type (in different method)
	public function setSource(Source $source): void
	{
		$this->source = $source;
	}


	public function render(): void
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/default.latte');

		$columns = $this->getColumns();
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
