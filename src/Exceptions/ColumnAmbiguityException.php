<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Column;

use function array_map;
use function implode;

final class ColumnAmbiguityException extends AbstractTableException
{
	public static function fromColumn(Column $column, Column|string $related): static
	{
		if ($related instanceof Column) {
			$related = $related->getName();
		}

		return new static('Column "'.$column->getName().'" creates ambiguity with "'.$related.'" in the table');
	}


	/**
	 * @param Column[] $columns
	 */
	public static function fromColumns(array $columns): static
	{
		$names = array_map(fn($x) => $x->getName(), $columns);
		$names = implode('", "', $names);

		return new static('Columns "'.$names.'" create ambiguity in the table');
	}
}
