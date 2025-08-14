<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Interfaces;

use Closure;
use JuniWalk\DataTable\Row;
use Nette\Utils\Html;

interface CustomRenderer
{
	public function setRenderer(?Closure $renderer = null): self;
	public function getRenderer(): ?Closure;
	public function hasRenderer(): bool;

	/**
	 * @return ($return is true ? Html|string|null : null)
	 */
	public function renderCustom(Row $row, Html|string $value = '', bool $return = false): Html|string|null;
}
