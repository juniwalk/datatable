<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Enums\Sort;

interface Source
{
	public function getItems(): iterable;
	public function getCount(): int;


	// todo: add docBlock with types
	public function filter(array $filter): void;

	/**
	 * @param array<string, Sort> $sort
	 */
	public function sort(array $sort): void;
}
