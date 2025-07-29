<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Filter;

class InvalidStateException extends \Exception
{
	public static function columnCustomRendererMissing(Column $column): self
	{
		return new self('Custom renderer callback for column "'.$column->getName().'" is not set.');
	}


	/**
	 * @param int[] $limits
	 */
	public static function limitUnknown(?int $limit, array $limits): self
	{
		return new self('Limit "'.$limit.'" must be one of "'.implode(', ', $limits).'".');
	}


	public static function limitsEmpty(): self
	{
		return new self('No valid page limits were given.');
	}


	public static function filterInputMissing(Filter $filter): self
	{
		return new self('Input for filter "'.$filter->getName().'" is missing.');
	}
}
