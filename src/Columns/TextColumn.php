<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Row;

class TextColumn extends AbstractColumn
{
	public function render(Row $row): void
	{
		$value = $row->getValue($this);

		// todo: check for stringable
		// todo: allow BackedEnums to be printed
		if (!is_scalar($value)) {
			throw new \Exception;
		}

		// convert to string
		echo $value;
	}
}
