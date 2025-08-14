<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Interfaces;

use Closure;
use JuniWalk\DataTable\Row;
use Nette\Utils\Html;

interface CallbackRenderable
{
	public function setRenderer(?Closure $renderer, bool $strict = false): self;
	public function getRenderer(): ?Closure;
	public function hasRenderer(): bool;

	public function renderCallback(Row $row, mixed ...$params): void;
	public function callbackRender(Row $row, mixed ...$params): ?Html;
}
