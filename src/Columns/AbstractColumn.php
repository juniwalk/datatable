<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use Closure;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Row;
use Nette\Application\UI\Control;

abstract class AbstractColumn extends Control implements Column
{
	/** @var array<string, Filter> */
	protected array $filters = [];
	protected bool $isFiltered = false;

	protected bool $isSortable;
	protected ?string $sortBy = null;
	protected ?Sort $sort;

	protected Align $align = Align::Left;

	private ?Closure $renderer = null;


	public function __construct(
		protected string $label,
	) {
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


	public function isFiltered(): bool
	{
		return $this->isFiltered;
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
		// ? Null value allows override from global sort
		return $this->isSortable ?? null;
	}


	public function setSortedBy(?string $sortBy): self
	{
		$this->sortBy = $sortBy;
		return $this;
	}


	public function getSortedBy(): ?string
	{
		return $this->sortBy;
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


	/**
	 * @param value-of<Align> $align
	 */
	public function setAlign(Align|string $align): self
	{
		$this->align = Align::make($align);
		return $this;
	}


	public function getAlign(): Align
	{
		return $this->align;
	}


	public function setRenderer(?Closure $renderer = null): self
	{
		$this->renderer = $renderer;
		return $this;
	}


	public function getRenderer(): ?Closure
	{
		return $this->renderer;
	}


	public function render(Row $row): void
	{
		$value = match (true) {
			isset($this->renderer) => call_user_func($this->renderer, $row->getItem()),
			default => $this->renderValue($row),
		};

		if ($value !== null && !is_scalar($value)) {
			// todo: throw ColumnValueTypeException
			throw new \Exception;
		}

		echo $value;
	}


	public function renderLabel(): void
	{
		echo $this->label;
	}
}
