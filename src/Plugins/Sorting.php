<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Exceptions\ColumnNotFoundException;
use Nette\Application\Attributes\Persistent;

trait Sorting
{
	/** @var array<string, Sort> */
	#[Persistent]
	public array $sort = [];

	/** @var array<string, Sort> */
	protected array $sortDefault = [];

	protected bool $isSortable = false;
	protected bool $isSortMultiple = false;


	/**
	 * @throws ColumnNotFoundException
	 */
	public function handleSort(string $column): void
	{
		if (!$column || !$this->getColumn($column, false)) {
			throw ColumnNotFoundException::fromName($column);
		}

		$sort = $this->getCurrentSort();
		$sort[$column] = match ($sort[$column] ?? null) {
			Sort::ASC	=> Sort::DESC,
			Sort::DESC	=> null,
			default		=> Sort::ASC,
		};

		$sortShouldReset = $this->isSortMultiple && sizeof($this->sortDefault) > 1;
		$sortIsDefault = $this->isDefaultSort();

		if ($sortIsDefault && !$sort[$column] && !$sortShouldReset) {
			$sort[$column] = Sort::ASC;
		}

		if (!$this->isSortMultiple) {
			$sort = [$column => $sort[$column]];
		}

		$this->sort = array_filter($sort);

		if ($this->isDefaultSort()) {
			$this->sort = [];
		}

		$this->redrawControl('table');
		$this->redirect('this');
	}


	public function setSortable(bool $sortable = true): self
	{
		$this->isSortable = $sortable;
		return $this;
	}


	public function isSortable(): bool
	{
		return $this->isSortable;
	}


	public function setSortMultiple(bool $sortMultiple = true): void
	{
		$this->isSortMultiple = $sortMultiple;
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
		return $this->sort ?: $this->sortDefault;
	}


	/**
	 * @param  array<string, Sort|value-of<Sort>> $sort
	 * @throws ColumnNotFoundException
	 */
	public function setDefaultSort(array $sort): self
	{
		$this->sortDefault = [];

		foreach ($sort as $column => $sort) {
			if (!$column || !$this->getColumn($column, false)) {
				throw ColumnNotFoundException::fromName($column);
			}

			$this->sortDefault[$column] = Sort::make($sort, true);
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
	 * @param array<string, mixed> $state
	 */
	public function loadState(array $state): void
	{
		$state['sort'] = (array) ($state['sort'] ?? []);
		$state['limit'] ??= null;

		if ($state['limit'] && !in_array($state['limit'], $this->limits)) {
			unset($state['limit']);
		}

		foreach ($state['sort'] as $column => $order) {
			unset($state['sort'][$column]);

			if ($sort = Sort::make($order, false)) {
				$state['sort'][$column] = $sort;
			}
		}

		parent::loadState($state);
	}


	protected function validateSorting(): void
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
