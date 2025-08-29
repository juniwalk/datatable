<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns\Traits;

use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Filter;

/**
 * @phpstan-require-implements Filterable
 */
trait Filters
{
	protected bool $isFiltered = false;

	/**
	 * @var array<string, Filter>
	 */
	protected array $filters = [];


	public function isFiltered(): bool
	{
		return $this->isFiltered;
	}


	public function addFilter(Filter $filter): static
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


	public function detectFilteredStatus(): void
	{
		$this->isFiltered = false;

		foreach ($this->filters as $filter) {
			if (!$filter->isFiltered()) {
				continue;
			}

			$this->isFiltered = true;
			break;
		}
	}
}
