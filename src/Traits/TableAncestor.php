<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Table;
use Throwable;

trait TableAncestor
{
	/**
	 * @return ($require is true ? Table : ?Table)
	 * @throws InvalidStateException
	 */
	protected function getTable(bool $require = true): ?Table
	{
		if ($this instanceof Table) {
			return $this;
		}

		try {
			return $this->lookup(Table::class, $require);	// @phpstan-ignore return.type (Missing generics in Nette)

		} catch (Throwable) {
		}

		throw InvalidStateException::parentRequired(Table::class, $this);
	}
}
