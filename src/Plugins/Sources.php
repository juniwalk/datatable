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
use Nette\Application\UI\Presenter;

trait Sources
{
	protected int|string|null $redrawItem = null;

	protected Source $source;


	public function redrawItem(int|string|null $id): static
	{
		$this->redrawItem = $id;
		return $this;
	}


	public function setSource(Source $source): static
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
			throw new SourceMissingException;
		}

		return $this->source;
	}


	public function addLoadCallback(callable $callback): static
	{
		$this->when('load', $callback);
		return $this;
	}


	public function addItemCallback(callable $callback): static
	{
		$this->when('item', $callback);
		return $this;
	}


	protected function createModel(): mixed { return null; }
	protected function createTable(): void {}


	/**
	 * @throws SourceUnknownException
	 */
	protected function validateSources(Presenter $presenter): void
	{
		if ($model = $this->createModel()) {
			$this->source = SourceFactory::fromModel($model);
		}

		$this->createTable();
	}
}
