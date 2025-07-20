<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Enums\Sort;
use Nette\Application\Attributes\Persistent;

trait Sorting
{
	/** @var array<non-empty-string, 'asc'|'desc'|null> */
	#[Persistent]
	public array $sort = [];

	private bool $isSortMultiple = false;

	// todo: setSortDefault - default sorting
	// todo: setSortable - allow to set whole datatable as sortable


	public function handleSort(string $column): void
	{
		if (!$column || !$this->getColumn($column, false)) {
			throw new \Exception;
		}

		$sort = $this->sort[$column] ?? null;
		$sort = Sort::make($sort, false);

		if (!$this->isSortMultiple) {
			$this->sort = [];
		}

		$this->sort[$column] = match ($sort) {
			Sort::ASC	=> Sort::DESC->value,
			Sort::DESC	=> null,
			null		=> Sort::ASC->value,
		};

		$this->redirect('this');
	}


	public function setSortMultiple(bool $sortMultiple = true): void
	{
		$this->isSortMultiple = $sortMultiple;
	}


	public function isSortMultiple(): bool
	{
		return $this->isSortMultiple;
	}
}
