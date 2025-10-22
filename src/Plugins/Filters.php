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
use JuniWalk\DataTable\Filters\NumberRangeFilter;
use JuniWalk\DataTable\Filters\SelectFilter;
use JuniWalk\DataTable\Filters\SelectListFilter;
use JuniWalk\Utils\Arrays;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;
use Nette\Application\UI\Template;

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

	/** @var array<string, FilterStruct> */
	protected array $filters = [];


	public function handleClearByFilter(string $filterName): void
	{
		$filter = $this->getFilter($filterName);
		$this->clearFilterValue($filter);

		$this->redrawControl();
		$this->redirect('this');
	}


	public function handleClearByColumn(string $columnName): void
	{
		foreach ($this->getFilters() as $filter) {
			if (!$filter->hasColumn($columnName)) {
				continue;
			}

			$this->clearFilterValue($filter);
		}

		$this->redrawControl();
		$this->redirect('this');
	}


	public function handleClearFilter(): void
	{
		foreach ($this->getFilters() as $filter) {
			$this->clearFilterValue($filter);
		}

		$this->setOption(Option::IsFiltered, false);

		$this->redrawControl();
		$this->redirect('this');
	}


	protected function handleFilter(): void
	{
		$this->setOption(Option::IsFiltered, true);
		$this->setPage(1);

		$this->filter = array_filter(
			callback: fn($x) => $x !== '' && $x !== null,
			array: Arrays::map($this->filters, function($filter, string $name) {
				$this->setFilterRedraw($name);
				return $filter->getValueFormatted();
			}),
		);

		if ($this->rememberState) {
			$this->setOption(Option::StateFilters, $this->filter ?: null);
		}

		$this->redrawControl('paginator');
		$this->redrawControl('toolbar');
		$this->redrawControl('table');
		$this->setFilterRedraw();
		$this->redirect('this');
	}


	public function setFilterRedraw(?string $name = null): static
	{
		$this->redrawControl('filters');

		if (!empty($name)) {
			$this->redrawControl('filter-'.$name.'-clear');
		}

		return $this;
	}


	public function setAutoSubmit(bool $autoSubmit): static
	{
		$this->autoSubmit = $autoSubmit;
		return $this;
	}


	public function isAutoSubmit(): bool
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
	public function addFilterEnumList(string $name, string $label, string $enum, string|array $columns = []): EnumListFilter
	{
		return $this->addFilter($name, new EnumListFilter($label, $enum), $columns);
	}


	/**
	 * @param string|string[] $columns
	 */
	public function addFilterNumberRange(string $name, string $label, string|array $columns = []): NumberRangeFilter
	{
		return $this->addFilter($name, new NumberRangeFilter($label), $columns);
	}


	/**
	 * @param  array<int|string, mixed> $items
	 * @param  string|string[] $columns
	 * @return SelectFilter
	 */
	public function addFilterSelect(string $name, string $label, array $items, string|array $columns = []): SelectFilter
	{
		return $this->addFilter($name, new SelectFilter($label), $columns)->setItems($items);
	}


	/**
	 * @param  array<int|string, mixed> $items
	 * @param  string|string[] $columns
	 * @return SelectListFilter
	 */
	public function addFilterSelectList(string $name, string $label, array $items, string|array $columns = []): SelectListFilter
	{
		return $this->addFilter($name, new SelectListFilter($label), $columns)->setItems($items);
	}


	/**
	 * @param string|string[] $columns
	 */
	public function addFilterText(string $name, string $label, string|array $columns = []): TextFilter
	{
		return $this->addFilter($name, new TextFilter($label), $columns);
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


	/**
	 * @throws FilterNotFoundException
	 */
	public function removeFilter(string $name): void
	{
		$this->getFilter($name)->setParent(null);
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
		$filterActive = Arrays::map($this->filters, function($filter) {
			if (!$filter->isFiltered()) {
				return null;
			}

			return $filter->getValue();
		});

		return ! (bool) array_udiff_assoc(
			$this->getDefaultFilter(),
			array_filter($filterActive),
			fn($a, $b) => $a <=> $b,
		);
	}


	/**
	 * @param FilterStruct $filter
	 */
	protected function clearFilterValue(Filter $filter): void
	{
		// if (!$filter->isFiltered()) {
		// 	throw FilterUnusedException::fromFilter($filter);
		// }

		unset($this->filter[$filter->getName()]);
		$filter->setValue(null);

		$this->setOption(Option::IsFiltered, true);
		$this->getComponent('filterForm')->reset();

		if ($this->rememberState) {
			$this->setOption(Option::StateFilters, $this->filter ?: null);
		}
	}


	protected function onRenderFilters(Template $template): void
	{
		$template->autoSubmit = $this->autoSubmit;
		$template->filters = $this->filters;

		if (!$this->filters) {
			return;
		}

		$current = $this->getCurrentFilter();

		foreach ($current as $name => $value) {
			if (!isset($this->filters[$name])) {
				continue;
			}

			$this->filters[$name]->setValue($value);
		}

		foreach ($this->getColumns() as $column) {
			if (!$column instanceof Filterable) {
				continue;
			}

			$column->detectFilteredStatus();
		}

		// ? Assign current filter values as defaults so they are kept on clear
		$this->getComponent('filterForm')->setDefaults($current, true);

		$this->addToolbarButton('__filter_toggle', 'datatable.filter.button', '__filters')
			->setIcon('fa-filter')->setClass('btn btn-sm btn-info collapsed')
			->setAttribute('data-bs-target', '#'.$this->getSnippetId('filters'))
			->setAttribute('data-bs-toggle', 'collapse');

		$this->addToolbarLink('__filter_clear', '', '__filters')->setLink('clearFilter!')
			->setIcon('fa-times')->setClass('btn btn-sm btn-info ajax')
			->setAttribute('data-bs-toggle', 'tooltip')
			->setTitle('datatable.filter.clear');

		$this->allowToolbarAction('__filter_clear', $this->shouldShowFilters());
	}


	protected function createComponentFilterForm(): Form
	{
		$form = new Form;
		$form->setTranslator($this->getTranslator());
		$form->addSubmit('__submit');
		$form->addProtection();

		// ? Filter values are set in onSuccess attached in Filter::attachToForm()
		Arrays::map($this->filters, fn($filter) => $filter->attachToForm($form));

		$form->onSuccess[] = $this->handleFilter(...);
		$form->onError[] = function($form) {
			Arrays::map($form->getErrors(), function($error) {
				$this->getPresenterIfExists()?->flashMessage($error, 'danger');
			});
		};

		return $form;
	}
}
