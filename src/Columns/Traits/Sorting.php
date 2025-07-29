<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns\Traits;

use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Enums\Sort;

/**
 * @phpstan-require-implements Sortable
 */
trait Sorting
{
	// ? Null value allows override from global sort
	protected ?bool $isSortable = null;

	protected ?Sort $sort = null;


	public function setSortable(bool|string $sortable): self
	{
		if (is_string($sortable) && $sortable <> '') {
			$this->field ??= $sortable;
		}

		$this->isSortable = (bool) $sortable;
		return $this;
	}


	public function isSortable(): ?bool
	{
		return $this->isSortable;
	}


	public function setSorted(?Sort $sort): self
	{
		$this->sort = $sort;
		return $this;
	}


	public function isSorted(): ?Sort
	{
		return $this->sort;
	}
}
