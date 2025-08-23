<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

// todo: do not extend Filter, be standalone
// todo: rename to FilterRange - als ocreate FilterList
interface FilterRanged extends Filter
{
	public function getValueFrom(): mixed;
	public function getValueTo(): mixed;

	// todo: add value methods with proper types of array{from, to}
}
