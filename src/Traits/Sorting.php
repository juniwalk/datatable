<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Enums\Sort;
use Nette\Application\Attributes\Persistent;

trait Sorting
{
	/** @var array<string, Sort> */
	#[Persistent]
	public array $sort = [];

	/** @var array<string, Sort> */
	private array $sortDefault = [];

	private bool $isSortable = false;
	private bool $isSortMultiple = false;


	public function handleSort(string $column): void
	{
		if (!$column || !$this->getColumn($column, false)) {
			// todo: throw new ColumnNotFoundException($column)
			throw new \Exception;
		}

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
	 * @param array<string, Sort|value-of<Sort>> $sort
	 */
	public function setDefaultSort(array $sort): self
	{
		$this->sortDefault = [];

		foreach ($sort as $column => $sort) {
			if (!$column || !$this->getColumn($column, false)) {
				// todo: throw new ColumnNotFoundException($column)
				throw new \Exception;
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
}
