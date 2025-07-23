<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filter;
use Nette\Application\UI\Control;

abstract class AbstractColumn extends Control implements Column
{
	/**
	 * @var array<string, Filter>
	 */
	protected array $filters = [];
	protected bool $isFiltered = false;


	protected bool $isSortable;

	// todo: use better name, as it will be used for filters too
	protected string $sortBy;

	protected ?Sort $sort;

	// todo: use new Align enum for this
	protected string $align = 'start';

	public function __construct(
		protected ?string $label,
	) {
	}


	public function isFiltered(): bool
	{
		return $this->isFiltered;
	}


	public function addFilter(Filter $filter): self
	{
		if ($filter->isFiltered()) {
			$this->isFiltered = true;
		}

		$this->filters[$filter->getName()] = $filter;
		return $this;
	}

	/**
	 * @return array<string, Filter>
	 */
	public function getFilters(): array
	{
		return $this->filters;
	}


	public function setSortable(bool|string $sortable): self
	{
		if (is_string($sortable) && $sortable <> '') {
			$this->sortBy = $sortable;
		}

		$this->isSortable = (bool) $sortable;
		return $this;
	}


	public function isSortable(): ?bool
	{
		return $this->isSortable ?? null;
	}


	public function getSortedBy(): ?string
	{
		return $this->sortBy ?? null;
	}


	public function setSorted(?Sort $sort): self
	{
		$this->sort = $sort;
		return $this;
	}


	public function isSorted(): ?Sort
	{
		return $this->sort ?? null;
	}


	public function setAlign(string $align): self
	{
		$this->align = $align;
		return $this;
	}


	public function getAlign(): string
	{
		return $this->align;
	}


	public function renderLabel(): void
	{
		echo $this->label;
	}
}
