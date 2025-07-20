<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use DateTimeInterface;

class DateColumn extends AbstractColumn
{
	protected string $align = 'end';


	public function render(mixed $row): void
	{
		if (!$row instanceof DateTimeInterface) {
			throw new \Exception;
		}

		echo $row->format('j. n. Y');
	}
}
