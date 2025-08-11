<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use BackedEnum;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Container;
use JuniWalk\DataTable\Enums\Option;
use JuniWalk\DataTable\Enums\Storage;
use JuniWalk\DataTable\Exceptions\FilterNotFoundException;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters\DateFilter;
use JuniWalk\DataTable\Filters\EnumFilter;
use JuniWalk\DataTable\Filters\TextFilter;
use JuniWalk\Utils\Arrays;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;

trait Filters
{
	/** @var array<string, scalar> */
	#[Persistent]
	public array $filter = [];

	/** @var array<string, scalar> */
	private array $filterDefault = [];

	private ?bool $isFilterShown = null;
	private ?int $filterColumnCount = null;


	public function handleClear(?string $column = null): void
	{
		foreach ($this->getFilters() as $filter) {
			if ($column && !$filter->hasColumn($column)) {
				continue;
			}

			$filter->setValue(null);
		}

		$this->setOption(Option::IsFiltered, $column <> null);
		$this->redirect('this');
	}


	public function setFilterShown(?bool $filterShown = true): self
	{
		$this->isFilterShown = $filterShown;
		return $this;
	}


	public function isFilterShown(): ?bool
	{
		return $this->isFilterShown;
	}


	public function shouldShowFilters(): bool
	{
		if ($this->isFilterShown !== null) {
			return $this->isFilterShown;
		}

		// todo: empty filter but not default one should still show them [?]
		return !empty($this->filter) && !$this->isDefaultFilter();
	}


	public function setFilterColumnCount(?int $filterColumnCount): self
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
	 * @param class-string<BackedEnum> $enum
	 * @param string|string[] $columns
	 */
	public function addFilterEnum(string $name, string $label, string $enum, string|array $columns = []): EnumFilter
	{
		return $this->addFilter($name, new EnumFilter($label, $enum), $columns);
	}


	/**
	 * @template T of Filter
	 * @param  T $filter
	 * @param  string|string[] $columns
	 * @return T
	 */
	public function addFilter(string $name, Filter $filter, string|array $columns = []): Filter
	{
		$columns = (array) $columns;
		$columns[] = $name;

		foreach ($columns as $key => $column) {
			$columns[$key] = $this->getColumn($column, false);
		}

		$columns = array_filter($columns, fn($x) => $x instanceof Column);

		/** @var T */
		$filter = $this->__filters()->add($name, $filter);
		$filter->setColumns(...$columns);

		return $filter;
	}


	/**
	 * @return ($require is true ? Filter : ?Filter)
	 */
	public function getFilter(string $name, bool $require = true): ?Filter
	{
		return $this->__filters()->get($name, $require);
	}


	/**
	 * @return array<string, Filter>
	 */
	public function getFilters(): array
	{
		return $this->__filters()->list();
	}


	public function removeFilter(string $name): void
	{
		$this->__filters()->remove($name);
	}


	/**
	 * @return array<string, scalar>
	 */
	public function getCurrentFilter(): array
	{
		if ($this->filter || $this->getOption(Option::IsFiltered)) {
			return $this->filter;
		}

		return $this->filterDefault;
	}


	/**
	 * @param  array<string, mixed> $filters
	 * @throws FilterNotFoundException
	 * @throws FilterValueInvalidException
	 */
	public function setDefaultFilter(array $filters): self
	{
		$this->filterDefault = [];

		foreach ($filters as $name => $query) {
			if (!$filter = $this->getFilter($name, false)) {
				throw FilterNotFoundException::fromName($name);
			}

			if (!$value = $filter->format($query)) {
				throw FilterValueInvalidException::fromFilter($filter, 'scalar', $query);
			}

			$this->filterDefault[$name] = $value;
		}

		return $this;
	}


	/**
	 * @return array<string, scalar>
	 */
	public function getDefaultFilter(): array
	{
		return $this->filterDefault;
	}


	// todo: this needs to be writen for filters which can be array
	public function isDefaultFilter(): bool
	{
		return !array_udiff_assoc(
			$this->getCurrentFilter(),
			$this->filterDefault,
			fn($a, $b) => $a <=> $b,
		);
	}


	protected function createComponentFilterForm(): Form
	{
		$form = new Form;
		$form->addSubmit('submit');

		foreach ($this->getFilters() as $filter) {
			$filter->attachToForm($form);
		}

		$form->onError[] = function($form) {
			Arrays::map($form->getErrors(), fn($msg) => $this->flashMessage($msg, 'danger'));
		};

		$form->onSuccess[] = function() {
			$this->setOption(Option::IsFiltered, true);
			$this->redirect('this');
		};

		return $form->setDefaults($this->filter);
	}


	/**
	 * @param  array<string, mixed> $state
	 * @return array<string, mixed>
	 */
	protected function loadStateFilters(array $state): array
	{
		return $state;
	}


	/**
	 * @param  array<string, mixed> $state
	 * @return array<string, mixed>
	 */
	protected function saveStateFilters(array $state): array
	{
		$state['filter'] = [];

		foreach ($this->getFilters() as $name => $filter) {
			$state['filter'][$name] = $filter->getValue();
		}

		return $state;
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

		$this->addToolbarButton('__filter_toggle', 'datatable.filter.button', '__filters')
			->setIcon('fa-filter')->setClass('btn btn-sm btn-info collapsed')
			->setAttribute('data-bs-target', '#'.$this->getSnippetId('filters'))
			->setAttribute('data-bs-toggle', 'collapse');

		$this->addToolbarLink('__filter_clear', '', '__filters')->setLink('clear!')
			->setIcon('fa-times')->setClass('btn btn-sm btn-info')
			->setAttribute('data-bs-toggle', 'tooltip')
			->setTitle('datatable.filter.cancel');

		$this->allowToolbarAction('__filter_clear', $this->shouldShowFilters());
	}


	/**
	 * @return Container<Filter>
	 */
	private function __filters(): Container
	{
		return $this->getComponent(Storage::Filters->value);
	}
}
