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
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use Nette\Application\UI\Template;

trait Ordering
{
	/**
	 * @param  int[] $delta
	 * @throws ColumnNotFoundException
	 * @throws ColumnSortRequiredException
	 * @throws InvalidStateException
	 */
	public function handleOrdering(array $delta): void
	{
		$delta = array_map('intval', $delta);
		$delta = array_filter($delta);

		if (!$column = $this->findOrderColumn()) {
			throw ColumnNotFoundException::fromClass(OrderColumn::class);
		}

		if (!$columnName = $column->getName()) {
			throw InvalidStateException::notAttached($column);
		}

		$sort = $this->getCurrentSort();

		if (sizeof($sort) > 1 || !$sortBy = $sort[$columnName] ?? null) {
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


	public function addOrderCallback(callable $callback): static
	{
		$this->when('order', $callback);
		return $this;
	}


	/**
	 * @throws ColumnAmbiguityException
	 */
	protected function findOrderColumn(): ?OrderColumn
	{
		return $this->getColumnByType(OrderColumn::class, false);
	}


	protected function onRenderOrdering(Template $template): void
	{
		if (!$column = $this->findOrderColumn()) {
			return;
		}

		$template->attributes['data-dt-allow-ordering'] = true;
		$template->signalOrdering = $this->link('ordering!');

		if (!in_array($column, $this->getColumnsSorted())) {
			$template->signalOrdering = false;
			$column->setDisabled(true);
		}
	}
}
