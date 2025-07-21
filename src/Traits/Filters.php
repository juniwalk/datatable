<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Column;
use Nette\Application\Attributes\Persistent;

/**
 * @phpstan-import-type ColumnName from Column
 */
trait Filters
{
	/** @var array<ColumnName, scalar> */
	#[Persistent]
	public array $filter = [];

	private bool $isFilterShown = false;


	// todo: addFilter*
	// todo: getFilter
	// todo: removeFilter

	// todo: setFilterDefault - default filters

	// todo: setFilterShown
	// todo: isFilterShown


	/**
	 * @param ColumnName $column
	 */
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
}
