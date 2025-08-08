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
	protected Source $source;


	public function setSource(Source $source): self
	{
		$this->source = $source;
		return $this;
	}


	/**
	 * @throws SourceMissingException
	 */
	public function getSource(): Source
	{
		if (!isset($this->source)) {
			// todo: give more details with the exception
			throw new SourceMissingException;
		}

		return $this->source;
	}


	/**
	 * @throws SourceMissingException
	 */
	public function addLoadCallback(callable $callback): self
	{
		if (!isset($this->source)) {
			// todo: give more details with the exception
			throw new SourceMissingException;
		}

		$this->source->when('load', $callback);
		return $this;
	}


	/**
	 * @throws SourceMissingException
	 */
	public function addItemCallback(callable $callback): self
	{
		if (!isset($this->source)) {
			// todo: give more details with the exception
			throw new SourceMissingException;
		}

		$this->source->when('item', $callback);
		return $this;
	}


	protected function createModel(): mixed { return null; }
	protected function createTable(): void {}


	/**
	 * @throws SourceUnknownException
	 */
	protected function validateSources(): void
	{
		if ($model = $this->createModel()) {
			$this->source = SourceFactory::fromModel($model);
		}

		$this->createTable();
	}
}
