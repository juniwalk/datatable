<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns\Traits;

use JuniWalk\DataTable\Columns\Interfaces\Hideable;

/**
 * @phpstan-require-implements Hideable
 */
trait Hiding
{
	protected ?bool $isHidden = null;
	protected bool $isHideDefault = false;


	public function setHidden(?bool $hidden): static
	{
		$this->isHidden = $hidden;
		return $this;
	}


	public function isHidden(): bool
	{
		return $this->isHidden ?? $this->isHideDefault;
	}


	public function setDefaultHide(bool $hideDefault = true): static
	{
		$this->isHideDefault = $hideDefault;
		return $this;
	}


	public function isDefaultHide(): bool
	{
		return $this->isHideDefault;
	}
}
