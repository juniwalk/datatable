<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

final class SourceUnknownException extends AbstractTableException
{
	public static function fromModel(mixed $model): static
	{
		return new static('Unable to find suitable Source from given model of type '.gettype($model).'.');
	}
}
