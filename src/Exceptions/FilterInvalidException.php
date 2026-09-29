<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Filter;

final class FilterInvalidException extends AbstractTableException
{
	public static function missingImplement(Filter $filter): static
	{
		return static::fromFilter($filter, 'has to implement one of FilterSingle, FilterRange or FilterList interfaces');
	}


	public static function unableToHandle(Filter $filter): static
	{
		return static::fromFilter($filter, 'could not be handled');
	}


	public static function notSearchable(Filter $filter): static
	{
		return static::fromFilter($filter, 'is not searchable');
	}


	public static function missingSearchProvider(Filter $filter): static
	{
		return static::fromFilter($filter, 'is missing a search provider');
	}


	protected static function fromFilter(Filter $filter, string $message): static
	{
		return new static('Filter "'.$filter->getName().'" of type "'.$filter::class.'" '.$message.'.');
	}
}
