<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

final class FieldNotFoundException extends AbstractTableException
{
	public static function fromName(string $field): static
	{
		return new static('Field "'.$field.'" not found in the row.');
	}
}
