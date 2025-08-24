<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Filter;

class FilterInvalidException extends \Exception
{
	public static function missingImplement(Filter $filter): self
	{
		return static::fromFilter($filter, 'has to implement one of FilterSingle, FilterRange or FilterList interfaces');
	}


	public static function unableToHandle(Filter $filter): self
	{
		return static::fromFilter($filter, 'could not be handled');
	}


	protected static function fromFilter(Filter $filter, string $message): self
	{
		return new self('Filter "'.$filter->getName().'" of type "'.$filter::class.'" '.$message.'.');
	}
}
