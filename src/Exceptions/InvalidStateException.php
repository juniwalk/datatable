<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Exceptions;

use JuniWalk\DataTable\Filter;
use JuniWalk\Utils\Format;
use Nette\ComponentModel\Component;

class InvalidStateException extends \Exception
{
	public static function customRendererMissing(Component $component): self
	{
		return new self('Custom renderer callback for "'.Format::className($component).'#'.$component->getName().'" is not set.');
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


	/**
	 * @param class-string $parent
	 * @param object $child
	 */
	public static function parentRequired(string $parent, object $child): self
	{
		return new self('Component '.$child::class.' needs to have access to '.$parent.' parent.');
	}
}
