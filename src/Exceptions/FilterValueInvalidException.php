<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Filter;
use Throwable;

/**
 * @phpstan-import-type FilterStruct from Filter
 */
final class FilterValueInvalidException extends AbstractTableException
{
	/**
	 * @param FilterStruct $filter
	 */
	public static function fromFilter(Filter $filter, string $expected, mixed $value = null, ?Throwable $previous = null): static
	{
		return new static('Filter "'.$filter->getName().'" has invalid value of type "'.gettype($value ?? $filter->getValue()).'", but "'.$expected.'" was expected.', previous: $previous);
	}
}
