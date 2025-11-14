<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Column;

final class ColumnSortRequiredException extends AbstractTableException
{
	public static function fromColumn(Column $column, bool $exclusive = false): static
	{
		return new static('Sort by a column "'.$column->getName().'" is required'.($exclusive ? ' and must be exclusive' : ''));
	}
}
