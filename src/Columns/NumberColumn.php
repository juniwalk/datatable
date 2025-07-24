<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Row;

class NumberColumn extends AbstractColumn
{
	protected string $align = 'end';


	// todo: add number formatting properties


	public function render(Row $row): void
	{
		$number = $row->getValue($this);

		if (!is_numeric($number)) {
			// todo: throw ColumnValueTypeException
			throw new \Exception;
		}

		echo number_format((float) $number);
	}
}
