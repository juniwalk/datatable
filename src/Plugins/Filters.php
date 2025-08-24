<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use BackedEnum;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Enums\Option;
use JuniWalk\DataTable\Exceptions\FilterInvalidException;
use JuniWalk\DataTable\Exceptions\FilterNotFoundException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters\DateFilter;
use JuniWalk\DataTable\Filters\DateRangeFilter;
use JuniWalk\DataTable\Filters\EnumFilter;
use JuniWalk\DataTable\Filters\EnumListFilter;
use JuniWalk\DataTable\Filters\TextFilter;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Filters\Interfaces\FilterRange;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\Utils\Arrays;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;

/**
 * @phpstan-import-type FilterStruct from Filter
 */
trait Filters
{
	/** @var array<string, mixed> */
	#[Persistent]
	public array $filter = [];

	/** @var array<string, mixed> */
	protected array $filterDefault = [];

	protected bool $autoSubmit = true;
	protected ?bool $isFilterShown = null;
	protected ?int $filterColumnCount = null;

	/** @var array<string, FilterStruct> */
	protected array $filters = [];


	public function handleClear(?string $column = null): void
	{
		foreach ($this->getFilters() as $name => $filter) {
			if ($column && !$filter->hasColumn($column)) {
				continue;
			}

			unset($this->filter[$name]);
			$filter->setValue(null);
		}

		$this->setOption(Option::IsFiltered, $column <> null);
		$this->getComponent('filterForm')->reset();

		$this->redrawControl();
		$this->redirect('this');
	}


	public function setAutoSubmit(bool $autoSubmit): static
	{
		$this->autoSubmit = $autoSubmit;
		return $this;
	}


	public function getAutoSubmit(): bool
	{
		return $this->autoSubmit;
	}


	public function setFilterShown(?bool $filterShown = true): static
	{
		$this->isFilterShown = $filterShown;
		return $this;
	}


	public function isFilterShown(): ?bool
	{
		return $this->isFilterShown;
	}


	public function isFiltered(): bool
	{
		return !empty($this->filter);
	}


	public function shouldShowFilters(): bool
	{
		if ($this->isFilterShown !== null) {
			return $this->isFilterShown;
		}

		return $this->isFiltered() || !$this->isDefaultFilter();
	}


	public function setFilterColumnCount(?int $filterColumnCount): static
	{
		$this->filterColumnCount = $filterColumnCount;
		return $this;
	}


	public function getFilterColumnCount(): ?int
	{
		return $this->filterColumnCount;
	}


	/**
	 * @param string|string[] $columns
	 */
	public function addFilterText(string $name, string $label, string|array $columns = []): TextFilter
	{
		return $this->addFilter($name, new TextFilter($label), $columns);
	}


	/**
	 * @param string|string[] $columns
	 */
	public function addFilterDate(string $name, string $label, string|array $columns = []): DateFilter
	{
		return $this->addFilter($name, new DateFilter($label), $columns);
	}


	/**
	 * @param string|string[] $columns
	 */
	public function addFilterDateRange(string $name, string $label, string|array $columns = []): DateRangeFilter
	{
		return $this->addFilter($name, new DateRangeFilter($label), $columns);
	}


	/**
	 * @template T of BackedEnum
	 * @param  class-string<T> $enum
	 * @param  string|string[] $columns
	 * @return EnumFilter<T>
	 */
	public function addFilterEnum(string $name, string $label, string $enum, string|array $columns = []): EnumFilter
	{
		return $this->addFilter($name, new EnumFilter($label, $enum), $columns);
	}


	/**
	 * @template T of BackedEnum
	 * @param  class-string<T> $enum
	 * @param  string|string[] $columns
	 * @return EnumListFilter<T>
	 */
	public function addFilterEnumMultiple(string $name, string $label, string $enum, string|array $columns = []): EnumListFilter
	{
		return $this->addFilter($name, new EnumListFilter($label, $enum), $columns);
	}


	/**
	 * @template T of Filter
	 * @param  T $filter
	 * @param  string|string[] $columns
	 * @return T
	 * @throws FilterInvalidException
	 */
	public function addFilter(string $name, Filter $filter, string|array $columns = []): Filter
	{
		if (!$filter instanceof FilterSingle &&
			!$filter instanceof FilterRange &&
			!$filter instanceof FilterList
		) {
			throw FilterInvalidException::missingImplement($filter);
		}

		$columns = (array) $columns;
		$columns[] = $name;

		foreach ($columns as $key => $column) {
			$columns[$key] = $this->getColumn($column, false);
		}

		$columns = array_filter($columns, fn($x) => $x instanceof Column);

		$filter->setParent($this, $name);
		$filter->setColumns(...$columns);

		$this->filters[$name] = $filter;
		return $filter;
	}


	/**
	 * @return ($require is true ? FilterStruct : ?FilterStruct)
	 * @throws FilterNotFoundException
	 */
	public function getFilter(string $name, bool $require = true): ?Filter
	{
		if ($require && !isset($this->filters[$name])) {
			throw FilterNotFoundException::fromName($name);
		}

		return $this->filters[$name] ?? null;
	}


	/**
	 * @return array<string, FilterStruct>
	 */
	public function getFilters(): array
	{
		return $this->filters;
	}


	public function removeFilter(string $name): void
	{
		$this->getFilter($name, false)?->setParent(null);
		unset($this->filters[$name]);
	}


	/**
	 * @return array<string, mixed>
	 */
	public function getCurrentFilter(): array
	{
		if ($this->filter || $this->getOption(Option::IsFiltered)) {
			return $this->filter;
		}

		return $this->filterDefault;
	}


	/**
	 * @param  array<string, mixed> $filterDefault
	 * @throws FilterNotFoundException
	 */
	public function setDefaultFilter(array $filterDefault): static
	{
		$this->filterDefault = [];

		foreach ($filterDefault as $name => $value) {
			if (!isset($this->filters[$name])) {
				throw FilterNotFoundException::fromName($name);
			}

			$this->filterDefault[$name] = $value;
		}

		return $this;
	}


	/**
	 * @return array<string, mixed>
	 */
	public function getDefaultFilter(): array
	{
		return $this->filterDefault;
	}


	public function isDefaultFilter(): bool
	{
		// todo: this needs to be writen for filters which can be array
		// todo: make sure formatting of the value is the same when comparing
		return ! (bool) array_udiff_assoc(
			$this->getDefaultFilter(),
			$this->getCurrentFilter(),
			fn($a, $b) => $a <=> $b,
		);
	}


	protected function createComponentFilterForm(): Form
	{
		$form = new Form;
		$form->setTranslator($this->getTranslator());
		$form->addProtection();
		$form->addSubmit('submit');

		foreach ($this->getFilters() as $filter) {
			$filter->attachToForm($form);
		}

		$form->onError[] = function($form) {
			Arrays::map($form->getErrors(), fn($msg) => $this->flashMessage($msg, 'danger'));
		};

		$form->onSuccess[] = function() {
			$this->setOption(Option::IsFiltered, true);
			$this->setPage(1);

			$this->filter = array_filter(
				array_map(fn($x) => $x->getValueFormatted(), $this->filters)
			);

			$this->redrawControl('paginator');
			$this->redrawControl('table');
			$this->redirect('this');
		};

		return $form;
	}


	protected function validateFilters(): void
	{
		$current = $this->getCurrentFilter();

		if (!$filters = $this->getFilters()) {
			return;
		}

		foreach ($filters as $name => $filter) {
			$filter->setValue($current[$name] ?? null);
		}

		foreach ($this->getColumns() as $column) {
			if (!$column instanceof Filterable) {
				continue;
			}

			$column->detectFilteredStatus();
		}

		$this->getComponent('filterForm')->setDefaults($current, true);

		$this->addToolbarButton('__filter_toggle', 'datatable.filter.button', '__filters')
			->setIcon('fa-filter')->setClass('btn btn-sm btn-info collapsed')
			->setAttribute('data-bs-target', '#'.$this->getSnippetId('filters'))
			->setAttribute('data-bs-toggle', 'collapse');

		$this->addToolbarLink('__filter_clear', '', '__filters')->setLink('clear!')
			->setIcon('fa-times')->setClass('btn btn-sm btn-info ajax')
			->setAttribute('data-bs-toggle', 'tooltip')
			->setTitle('datatable.filter.cancel');

		$this->allowToolbarAction('__filter_clear', $this->shouldShowFilters());
	}
}
