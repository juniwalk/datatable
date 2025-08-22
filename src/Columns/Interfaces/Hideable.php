<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns\Interfaces;

use JuniWalk\DataTable\Column;

interface Hideable extends Column
{
	public function setHidden(?bool $hidden): static;
	public function isHidden(): bool;

	public function setDefaultHide(bool $defaultHide = true): static;
	public function isDefaultHide(): bool;
}
