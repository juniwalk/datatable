<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Exceptions\SourceMissingException;
use JuniWalk\DataTable\Source;

trait Sources
{
	private Source $source;

	// todo: allow dynamic source creation from given data type (in different method)
	public function setSource(Source $source): self
	{
		$this->source = $source;
		return $this;
	}


	public function getSource(): ?Source
	{
		return $this->source ?? null;
	}


	protected function validateSources(): void
	{
		if (!isset($this->source)) {
			// todo: give more details with the exception
			throw new SourceMissingException;
		}
	}
}
