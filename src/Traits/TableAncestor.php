<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Table;

trait TableAncestor
{
	/**
	 * @return ($require is true ? Table : ?Table)
	 * @throws InvalidStateException
	 */
	protected function getTable(bool $require = true): ?Table
	{
		$parent = $this->getParent();

		if ($require && !$parent) {
			throw InvalidStateException::parentRequired(Table::class, $this);
		}

		if ($parent && !$parent instanceof Table) {
			throw InvalidStateException::parentRequired(Table::class, $this);
		}

		return $parent;
	}
}
