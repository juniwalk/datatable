<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\DataTable\Filters\Interfaces\FilterRange;
use Throwable;

final class FilterValueInvalidException extends AbstractTableException
{
	public static function fromFilter(
		FilterSingle|FilterRange|FilterList $filter,
		string $expected,
		mixed $value = null,
		?Throwable $previous = null,
	): static {
		return new static('Filter "'.$filter->getName().'" has invalid value of type "'.gettype($value ?? $filter->getValue()).'", but "'.$expected.'" was expected.', previous: $previous);
	}
}
