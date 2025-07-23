<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Container;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters\DateFilter;
use JuniWalk\DataTable\Filters\TextFilter;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;

/**
 * @phpstan-import-type ColumnName from Column
 */
trait Filters
{
	/** @var array<ColumnName, scalar> */
	#[Persistent]
	public array $filter = [];

	private bool $isFilterShown = false;
	private ?int $filterColumnCount = null;


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


	public function addFilterText(string $name, ?string $label): TextFilter
	{
		return $this->addFilter($name, new TextFilter($label));
	}


	public function addFilterDate(string $name, ?string $label): DateFilter
	{
		return $this->addFilter($name, new DateFilter($label));
	}


	/**
	 * @template T of Filter
	 * @param  T $filter
	 * @return T
	 */
	public function addFilter(string $name, Filter $filter): Filter
	{
		$this[Container::Filters]->addComponent($filter, $name);
		return $filter;
	}


	/**
	 * @return ($require is true ? Filter : ?Filter)
	 */
	public function getFilter(string $name, bool $require = true): ?Filter
	{
		return $this[Container::Filters]->getComponent($name, $require);
	}


	/**
	 * @return array<string, Filter>
	 */
	public function getFilters(): array
	{
		$filters = $this[Container::Filters]->getComponents(null, Filter::class);

		/** @var array<string, Filter> */
		return iterator_to_array($filters);
	}


	public function removeFilter(string $name): void
	{
		// todo: make sure this works properly as there was PHPStan issue
		$this[Container::Filters]->removeComponent($this->getFilter($name));
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
			$filter->createInput($form);
		}

		$form->onError[] = function($form) {
			foreach ($form->getErrors() as $message) {
				$this->flashMessage($message, 'danger');
			}
		};

		$form->onSuccess[] = function($form, $data) {
			// todo: add value formatting for each filter || stringify each value using Format class

			$this->filter = (array) $data;
			$this->redirect('this');
		};

		return $form->setDefaults($this->filter);
	}
}
