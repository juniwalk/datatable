<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use BackedEnum;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Container;
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

	private bool $isFilterShown = false;
	private ?int $filterColumnCount = null;


	public function handleClear(?string $column = null): void
	{
		foreach ($this->getFilters() as $name => $filter) {
			if ($column && !in_array($column, $filter->getColumns())) {
				continue;
			}

			unset($this->filter[$name]);
			$filter->setValue(null);
		}

		$this->redirect('this');
	}


	// todo: Enum::Always | Enum::WhenFiltered | Enum::Hide
	public function setFilterShown(bool $filterShown = true): self
	{
		$this->isFilterShown = $filterShown;
		return $this;
	}


	public function isFilterShown(): bool
	{
		return $this->isFilterShown;
	}


	public function shouldShowFilters(): bool
	{
		// todo: handle Enum::Always etc

		return $this->isFilterShown && !empty($this->filter);	// && !$this->isDefaultFilter();
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


	public function addFilterText(string $name, string $label): TextFilter
	{
		return $this->addFilter($name, new TextFilter($label));
	}


	public function addFilterDate(string $name, string $label): DateFilter
	{
		return $this->addFilter($name, new DateFilter($label));
	}


	/**
	 * @param class-string<BackedEnum> $enum
	 */
	public function addFilterEnum(string $name, string $label, string $enum): EnumFilter
	{
		return $this->addFilter($name, new EnumFilter($label, $enum));
	}


	/**
	 * @template T of Filter
	 * @param  T $filter
	 * @return T
	 */
	public function addFilter(string $name, Filter $filter): Filter
	{
		return $this->__filters()->add($name, $filter);
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


	// todo: getCurrentFilter
	// todo: setDefaultFilter - default filters
	// todo: getDefaultFilter
	// todo: isDefaultFilter


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
		$state['filter'] = (array) ($state['filter'] ?? []);

		foreach ($this->getFilters() as $name => $filter) {
			$state['filter'][$name] = $filter->getValue();
		}

		$state['filter'] = array_filter($state['filter'], fn($x) => $x !== '');

		return $state;
	}


	protected function validateFilters(): void
	{
		$columns = $this->getColumns();
		$filters = $this->getFilters();

		foreach ($filters as $name => $filter) {
			$filter->setValue($this->filter[$name] ?? null);

			foreach ($filter->getColumns() as $column) {
				$column = $columns[$column] ?? null;

				if (!$column || !$column instanceof Filterable) {
					continue;
				}

				$column->addFilter($filter);
			}
		}
	}


	/**
	 * @return Container<Filter>
	 */
	private function __filters(): Container
	{
		return $this->getComponent(Container::Filters);
	}
}
