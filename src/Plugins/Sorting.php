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

		// todo: sorting default sort does not work
		$sort = $this->getCurrentSort();
		$sort[$column] = match ($sort[$column] ?? null) {
			Sort::ASC	=> Sort::DESC,
			Sort::DESC	=> null,
			null		=> Sort::ASC,
		};

		if (!$this->isSortMultiple) {
			$sort = [$column => $sort[$column]];
		}

		$this->sort = array_filter($sort);

		if ($this->isDefaultSort()) {
			$this->sort = [];
		}

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
		return !array_udiff_assoc(
			$this->getCurrentSort(),
			$this->sortDefault,
			fn($a, $b) => $a <=> $b,
		);
	}


	/**
	 * @param  array<string, mixed> $state
	 * @return array<string, mixed>
	 */
	protected function loadStateSorting(array $state): array
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

		return $state;
	}


	/**
	 * @param  array<string, mixed> $state
	 * @return array<string, mixed>
	 */
	protected function saveStateSorting(array $state): array
	{
		return $state;
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
