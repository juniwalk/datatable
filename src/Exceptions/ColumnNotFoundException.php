<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

class ColumnNotFoundException extends \Exception
{
	public static function fromName(string $name): self
	{
		return new self('Column "'.$name.'" not found in the table.');
	}
}
