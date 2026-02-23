<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Exceptions\SourceMissingException;
use JuniWalk\DataTable\Exceptions\SourceUnknownException;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Source;
use JuniWalk\DataTable\SourceFactory;
use Nette\Application\UI\Presenter;

trait Sources
{
	protected int|string|null $itemRedraw = null;

	protected Source $source;


	public function setItemRedraw(int|string|null $id, bool $detail = false): static
	{
		$this->itemRedraw = $id;
		$snippetId = 'row-'.$id;

		if ($detail === true) {
			$snippetId .= '-detail';
		}

		if (!empty($id)) {
			$this->redrawControl('rows');
			$this->redrawControl($snippetId);
		}

		return $this;
	}


	public function isItemRedraw(int|string $id): bool
	{
		return $this->itemRedraw === $id;
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
	protected function createFilters(): void {}
	protected function createActions(): void {}
	protected function applySettings(): void {}


	/**
	 * @return Row[]
	 */
	protected function getRows(): array
	{
		$source = $this->getSource();

		$items = isset($this->itemRedraw)
			? $source->fetchItem($this->itemRedraw)
			: $source->fetchItems(
				$this->getFilters(),
				$this->getColumnsSorted(),
				$this->getOffset(),
				$this->getCurrentLimit(),
			);

		$this->trigger('load', $items, $source);

		$primaryKey = $source->getPrimaryKey();
		$rows = [];

		foreach ($items as $item) {
			$rows[] = $row = new Row($item, $primaryKey);
			$this->trigger('item', $item, $row);
		}

		return $rows;
	}


	/**
	 * @throws SourceUnknownException
	 */
	protected function validateSources(Presenter $presenter): void
	{
		if (($model = $this->createModel()) !== null) {
			$this->source = SourceFactory::fromModel($model);
		}

		$this->createTable();
		$this->createFilters();
		$this->createActions();

		$this->applySettings();
	}
}
