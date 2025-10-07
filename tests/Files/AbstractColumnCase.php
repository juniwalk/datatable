<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use InvalidArgumentException;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filters\TextFilter;
use JuniWalk\DataTable\Interfaces\CallbackRenderable;
use JuniWalk\DataTable\Row;
use Nette\Utils\Helpers;
use Tester\Assert;
use Tester\TestCase;

abstract class AbstractColumnCase extends TestCase
{
	/** @var class-string<Column> */
	protected string $className;


	public function testBasics(): void
	{
		$column = $this->createColumn('col', 'Column');
		$column->setAlign(Align::Left);
		$column->setField('e.column');

		Assert::same('col', $column->getName());
		Assert::same('Column', $column->getLabel());
		Assert::same('e.column', $column->getField());
		Assert::same(Align::Left, $column->getAlign());
	}


	public function testRender_Callback(): void
	{
		$column = $this->createColumn('col', 'Column');

		if (!$column instanceof CallbackRenderable) {
			return;
		}

		$output = Helpers::capture(function() use ($column) {
			$row = new Row(ItemsData[0], 'id');

			$column->setRenderer(fn() => 'Custom output');
			$column->render($row);
		});

		Assert::same('Custom output', $output);
	}


	public function testTraits_Sorting(): void
	{
		$column = $this->createColumn('col', 'Column');
		Assert::false($column->isSortable() ?? false);

		if (!$column instanceof Interfaces\Sortable) {
			return;
		}

		Assert::null($column->isSortable());

		$column->setSortable('e.name');
		Assert::same('e.name', $column->getField());
		Assert::true($column->isSortable());

		$column->setSorted(Sort::DESC);
		Assert::same(Sort::DESC, $column->isSorted());
	}


	public function testTraits_Filters(): void
	{
		$column = $this->createColumn('col', 'Column');
		Assert::false($column->isFiltered());

		if (!$column instanceof Interfaces\Filterable) {
			return;
		}

		$filter = new TextFilter('Name');
		$filter->setParent(null, 'name');

		$column->addFilter($filter);
		Assert::false($column->isFiltered());
		Assert::hasKey('name', $column->getFilters());

		$filter->setValue('John Doe');

		$column->detectFilteredStatus();
		Assert::true($column->isFiltered());
	}


	public function testTraits_Hiding(): void
	{
		$column = $this->createColumn('col', 'Column');
		Assert::false($column->isHidden());

		if (!$column instanceof Interfaces\Hideable) {
			return;
		}

		$column->setDefaultHide(true);
		Assert::true($column->isDefaultHide());
		Assert::true($column->isHidden());

		$column->setHidden(false);
		Assert::false($column->isHidden());
	}


	/**
	 * @param  mixed[] $args
	 * @throws InvalidArgumentException
	 */
	protected function createColumn(string $name, string $label, array $args = []): Column
	{
		if (!isset($this->className)) {
			throw new InvalidArgumentException('Missing className of the Column');
		}

		$column = new ($this->className)($label, ...$args);

		$table = (new TestPresenter)->getComponent('table');
		$table->addColumn($name, $column);

		return $column;
	}
}
