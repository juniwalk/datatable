<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

interface FilterRanged extends Filter
{
	public function getValueFrom(): mixed;
	public function getValueTo(): mixed;
}
