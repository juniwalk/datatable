<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Columns\OrderColumn;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Exceptions\ColumnAmbiguityException;
use JuniWalk\DataTable\Exceptions\ColumnNotFoundException;
use JuniWalk\DataTable\Exceptions\ColumnSortRequiredException;
use Nette\Application\UI\Template;

trait Ordering
{
	/**
	 * @param  int[] $delta
	 * @throws ColumnNotFoundException
	 * @throws ColumnSortRequiredException
	 */
	public function handleOrderDelta(array $delta): void
	{
		$delta = array_map('intval', $delta);
		$delta = array_filter($delta);

		if (!$column = $this->findColumnOrder()) {
			throw ColumnNotFoundException::fromClass(OrderColumn::class);
		}

		$sort = $this->getCurrentSort();

		if (sizeof($sort) > 1 || !$sortBy = $sort[$column->getName()] ?? null) {
			throw ColumnSortRequiredException::fromColumn($column, true);
		}

		if ($sortBy === Sort::DESC) {
			// ? Inverse delta values when sorting by DESC
			$delta = array_map(fn($x) => $x * -1, $delta);
		}

		$items = $this->source->fetchItem(...array_keys($delta));

		$this->trigger('order', $items, $delta);
		$this->source->clear();

		$this->redrawControl('table');
		$this->redirect('this');
	}


	/**
	 * @throws ColumnAmbiguityException
	 */
	public function addColumnOrder(string $name, string $label): OrderColumn
	{
		if ($column = $this->findColumnOrder()) {
			throw ColumnAmbiguityException::fromColumn($column, $name);
		}

		return $this->addColumn($name, new OrderColumn($label));
	}


	/**
	 * @throws ColumnAmbiguityException
	 */
	public function findColumnOrder(): ?OrderColumn
	{
		$columns = array_filter($this->columns, fn($x) => $x instanceof OrderColumn);

		if (sizeof($columns) > 1) {
			throw ColumnAmbiguityException::fromColumns($columns);
		}

		return array_values($columns)[0] ?? null;
	}


	public function addOrderCallback(callable $callback): static
	{
		$this->when('order', $callback);
		return $this;
	}


	protected function onRenderOrdering(Template $template): void
	{
		if (!$column = $this->findColumnOrder()) {
			return;
		}

		$template->attributes['data-dt-allow-ordering'] = $this->link('orderDelta!');

		if (!in_array($column, $this->getColumnsSorted())) {
			$column->setDisabled(true);
		}
	}
}
