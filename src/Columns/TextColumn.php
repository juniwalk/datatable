<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

class TextColumn extends AbstractColumn
{
	public function render(mixed $row): void
	{
		// todo: check for stringable
		if (!is_scalar($row)) {
			throw new \Exception;
		}

		// convert to string
		echo $row;
	}
}
