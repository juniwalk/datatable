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
		// convert to string
		echo $row;
	}
}
