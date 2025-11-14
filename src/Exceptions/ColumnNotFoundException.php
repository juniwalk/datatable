<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Column;

final class ColumnNotFoundException extends AbstractTableException
{
	/**
	 * @param class-string<Column> $class
	 */
	public static function fromClass(string $class): static
	{
		return new static('Column "'.$class.'" not found in the table.');
	}


	public static function fromName(string $name): static
	{
		return new static('Column "'.$name.'" not found in the table.');
	}
}
