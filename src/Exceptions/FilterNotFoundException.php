<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

class FilterNotFoundException extends \Exception
{
	public static function fromName(string $name): self
	{
		return new self('Filter "'.$name.'" not found in the table.');
	}
}
