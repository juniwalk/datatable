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
	protected bool|string $isSortable = false;
	protected ?Sort $sort;

	protected bool $isFiltered = false;
	protected mixed $filter;

	protected string $align = 'start';

	public function __construct(
		protected ?string $label,
	) {
	}


	public function setSort(Sort|string|null $sort): self
	{
		$this->sort = Sort::make($sort, false);
		return $this;
	}


	public function getSort(): ?Sort
	{
		return $this->sort ?? null;
	}


	public function setSortable(bool|string $sortable): self
	{
		$this->isSortable = $sortable;
		return $this;
	}


	public function isSortable(): bool
	{
		return (bool) $this->isSortable;
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
