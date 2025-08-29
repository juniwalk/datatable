<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

final class FilterNotFoundException extends AbstractTableException
{
	public static function fromName(string $name): static
	{
		return new static('Filter "'.$name.'" not found in the table.');
	}
}
