<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use BackedEnum;
use JuniWalk\DataTable\Row;
use Stringable;

class TextColumn extends AbstractColumn
{
	public function render(Row $row): void
	{
		$value = $row->getValue($this);

		if ($value instanceof Stringable) {
			$value = (string) $value;
		}

		if ($value instanceof BackedEnum) {
			$value = $value->value;
		}

		if (!is_scalar($value)) {
			// todo: throw ColumnValueTypeException
			throw new \Exception;
		}

		// convert to string
		echo $value;
	}
}
