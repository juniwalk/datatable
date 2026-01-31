<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Enums\Option;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Exceptions\ColumnNotFoundException;
use JuniWalk\DataTable\Exceptions\ColumnNotSortableException;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Template;

trait Sorting
{
	/** @var array<string, Sort> */
	#[Persistent]
	public array $sort = [] {
		set => array_filter(array_map(fn($sort) => Sort::make($sort, false), $value));
	}

	/** @var array<string, Sort> */
	protected array $sortDefault = [];

	protected bool $isSortable = false;
	protected bool $isSortMultiple = false;


	/**
	 * @throws ColumnNotFoundException
	 * @throws ColumnNotSortableException
	 */
	public function handleSort(string $column): void
	{
		if (!($this->getColumn($column)->isSortable() ?? $this->isSortable)) {
			throw ColumnNotSortableException::fromName($column);
		}

		$sort = $this->getCurrentSort();
		$sort[$column] = match ($sort[$column] ?? null) {
			Sort::ASC	=> Sort::DESC,
			Sort::DESC	=> null,
			default		=> Sort::ASC,
		};

		if (!$this->isSortMultiple) {
			$sort = [$column => $sort[$column]];
		}

		$this->setOption(Option::IsSorted, true);
		$this->sort = $sort;	// @phpstan-ignore assign.propertyType (null is not accepted but it is filtered in setter)

		if ($this->rememberState) {
			$this->setOption(Option::StateSorting, $this->sort ?: null);
		}

		$this->redrawControl('paginator');
		$this->redrawControl('toolbar');
		$this->redrawControl('table');
		$this->redirect('this');
	}


	public function handleClearSort(): void
	{
		$this->setOption(Option::IsSorted, false);
		$this->sort = [];

		if ($this->rememberState) {
			$this->setOption(Option::StateSorting, $this->sort ?: null);
		}

		$this->redrawControl('paginator');
		$this->redrawControl('table');
		$this->redirect('this');
	}


	public function setSortable(bool $sortable = true): static
	{
		$this->isSortable = $sortable;
		return $this;
	}


	public function isSortable(): bool
	{
		return $this->isSortable;
	}


	public function setSortMultiple(bool $sortMultiple = true): static
	{
		$this->isSortMultiple = $sortMultiple;
		return $this;
	}


	public function isSortMultiple(): bool
	{
		return $this->isSortMultiple;
	}


	/**
	 * @return array<string, Sort>
	 */
	public function getCurrentSort(): array
	{
		if ($this->sort || $this->getOption(Option::IsSorted)) {
			return $this->sort;
		}

		return $this->sortDefault;
	}


	/**
	 * @param  array<string, Sort|value-of<Sort>> $sortDefault
	 * @throws ColumnNotFoundException
	 * @throws ColumnNotSortableException
	 */
	public function setDefaultSort(array $sortDefault): static
	{
		$this->sortDefault = [];

		foreach ($sortDefault as $name => $sort) {
			$column = $this->getColumn($name);

			if (!$this->isSortable && !$column->isSortable()) {
				throw ColumnNotSortableException::fromName($name);
			}

			$this->sortDefault[$name] = Sort::make($sort, true);
		}

		return $this;
	}


	/**
	 * @return array<string, Sort>
	 */
	public function getDefaultSort(): array
	{
		return $this->sortDefault;
	}


	public function isDefaultSort(): bool
	{
		$sortCurrent = $this->getCurrentSort();

		foreach ($this->sortDefault as $column => $sort) {
			$current = $sortCurrent[$column] ??= null;

			if ($sort !== $current) {
				continue;
			}

			unset($sortCurrent[$column]);
		}

		return empty($sortCurrent);
	}


	/**
	 * @return Column[]
	 */
	protected function getColumnsSorted(): array
	{
		$sorting = [];

		foreach ($this->getCurrentSort() as $name => $sort) {
			$sorting[$name] = $this->getColumn($name, false);
		}

		return array_filter($sorting);
	}


	protected function onRenderSorting(Template $template): void
	{
		$sort = $this->getCurrentSort();

		foreach ($this->getColumns() as $name => $column) {
			if (!$column instanceof Sortable) {
				continue;
			}

			$column->setSorted($sort[$name] ?? null);

			// ? Set column as sortable only if there is no override
			if ($this->isSortable() && $column->isSortable() === null) {
				$column->setSortable(true);
			}
		}
	}
}
