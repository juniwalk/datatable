<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Filter;

class FilterUnknownException extends \Exception
{
	public static function fromFilter(Filter $filter): self
	{
		return new self('Filter "'.$filter->getName().'" of type "'.$filter::class.'" could not be handled.');
	}
}
