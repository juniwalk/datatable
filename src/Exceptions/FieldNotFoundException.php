<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

class FieldNotFoundException extends \Exception
{
	public static function fromName(string $field): self
	{
		return new self('Field "'.$field.'" not found in the row.');
	}
}
