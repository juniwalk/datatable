<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters\TextFilter;
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


	public function setFilterShown(bool $filterShown = true): self
	{
		$this->isFilterShown = $filterShown;
		return $this;
	}


	public function isFilterShown(): bool
	{
		return $this->isFilterShown;
	}


	public function addFilterText(string $name, ?string $label): TextFilter
	{
		return $this->addFilter($name, new TextFilter($label));
	}


	/**
	 * @template T of Filter
	 * @param  T $filter
	 * @return T
	 */
	public function addFilter(string $name, Filter $filter): Filter
	{
		$this->addComponent($filter, $name);
		return $filter;
	}


	/**
	 * @return ($require is true ? Filter : ?Filter)
	 */
	public function getFilter(string $name, bool $require = true): ?Filter
	{
		return $this->getComponent($name, $require);
	}


	/**
	 * @return array<string, Filter>
	 */
	public function getFilters(): array
	{
		$filters = $this->getComponents(null, Filter::class);

		/** @var array<string, Filter> */
		return iterator_to_array($filters);
	}


	public function removeFilter(string $name): void
	{
		// todo: make sure this works properly as there was PHPStan issue
		$this->removeComponent($this->getFilter($name));
	}


	// todo: getCurrentFilter
	// todo: setDefaultFilter - default filters
	// todo: getDefaultFilter
	// todo: isDefaultFilter
}
