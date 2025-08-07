<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Exceptions\SourceMissingException;
use JuniWalk\DataTable\Exceptions\SourceUnknownException;
use JuniWalk\DataTable\Source;
use JuniWalk\DataTable\SourceFactory;

trait Sources
{
	private Source $source;


	public function setSource(Source $source): self
	{
		$this->source = $source;
		return $this;
	}


	public function getSource(): ?Source
	{
		return $this->source ?? null;
	}


	protected function createModel(): mixed { return null; }
	protected function createTable(): void {}


	/**
	 * @throws SourceMissingException
	 * @throws SourceUnknownException
	 */
	protected function validateSources(): void
	{
		if ($model = $this->createModel()) {
			$this->source = SourceFactory::fromModel($model);
		}

		$this->createTable();

		if (!isset($this->source)) {
			// todo: give more details with the exception
			throw new SourceMissingException;
		}
	}
}
