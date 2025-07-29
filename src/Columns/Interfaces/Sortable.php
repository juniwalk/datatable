<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns\Interfaces;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Enums\Sort;

interface Sortable extends Column
{
	public function setSortable(bool|string $sortable): self;
	public function isSortable(): ?bool;

	public function setSorted(?Sort $sort): self;
	public function isSorted(): ?Sort;
}
