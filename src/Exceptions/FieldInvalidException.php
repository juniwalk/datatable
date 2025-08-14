<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Column;
use JuniWalk\Utils\Enums\Casing;
use JuniWalk\Utils\Format;
use Nette\ComponentModel\Component;

class FieldInvalidException extends \Exception
{
	public static function fromColumn(Column $column, mixed $value, string $expected): self
	{
		return self::fromName($column->getField() ?? $column->getName() ?? '', $value, $expected);
	}


	public static function fromName(string $field, mixed $value, string $expected): self
	{
		return new self('Field "'.$field.'" has invalid value of type "'.gettype($value).'", but "'.$expected.'" was expected.');
	}


	public static function fromComponent(Component $component, mixed $value, string $expected): self
	{
		return new self('Value of "'.Format::className($component, Casing::Pascal).'#'.$component->getName().'" has invalid type "'.gettype($value).'", but "'.$expected.'" was expected.');
	}
}
