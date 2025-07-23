<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Enums\Sort;
use Nette\Application\UI\Control;

abstract class AbstractColumn extends Control implements Column
{
	protected bool $isSortable;
	protected string $sortBy;
	protected ?Sort $sort;

	protected bool $isFiltered = false;
	protected mixed $filter;

	// todo: use new Align enum for this
	protected string $align = 'start';

	public function __construct(
		protected ?string $label,
	) {
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



	public function setFilter(mixed $filter): self
	{
		$this->filter = $filter;
		return $this;
	}


	public function getFilter(): mixed
	{
		return $this->filter ?? null;
	}


	public function setFiltered(bool $filtered): self
	{
		$this->isFiltered = $filtered;
		return $this;
	}


	public function isFiltered(): bool
	{
		return $this->isFiltered;
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
