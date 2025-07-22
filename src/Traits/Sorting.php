<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Enums\Sort;
use Nette\Application\Attributes\Persistent;

/**
 * @phpstan-import-type ColumnName from Column
 */
trait Sorting
{
	/** @var array<ColumnName, ?Sort> */
	#[Persistent]
	public array $sort = [];

	private bool $isSortable = false;
	private bool $isSortMultiple = false;

	// todo: setSortDefault - default sorting


	/**
	 * @param ColumnName $column
	 */
	public function handleSort(string $column): void
	{
		if (!$column || !$this->getColumn($column, false)) {
			throw new \Exception;
		}

		$sort = $this->sort[$column] ?? null;

		if (!$this->isSortMultiple) {
			$this->sort = [];
		}

		$this->sort[$column] = match ($sort) {
			Sort::ASC	=> Sort::DESC,
			Sort::DESC	=> null,
			null		=> Sort::ASC,
		};

		// todo: if the sort list is empty, use default sort
		// if (!array_filter($this->sort)) {
		// 	$this->sort = $this->sortDefault;
		// }

		$this->redirect('this');
	}


	public function setSortable(bool $sortable = true): self
	{
		$this->isSortable = $sortable;
		return $this;
	}


	public function isSortable(): bool
	{
		return $this->isSortable;
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
