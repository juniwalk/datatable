<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

final class ColumnNotSortableException extends AbstractTableException
{
	public static function fromName(string $name): static
	{
		return new static('Column "'.$name.'" is not sortable.');
	}
}
