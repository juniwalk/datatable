<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

class SourceUnknownException extends \Exception
{
	public static function fromModel(mixed $model): self
	{
		return new self('Unable to find suitable Source from given model of type '.gettype($model).'.');
	}
}
