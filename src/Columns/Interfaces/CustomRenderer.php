<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns\Interfaces;

use Closure;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Row;
use Nette\Utils\Html;

interface CustomRenderer extends Column
{
	public function setRenderer(?Closure $renderer = null): self;
	public function getRenderer(): ?Closure;
	public function hasRenderer(): bool;

	public function renderCustom(Row $row, Html|string $value): mixed;
}
