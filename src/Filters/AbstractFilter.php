<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use JuniWalk\DataTable\Filter;
use Nette\Application\UI\Control;

abstract class AbstractFilter extends Control implements Filter
{
	public function __construct(
		protected ?string $label,
	) {
	}
}
