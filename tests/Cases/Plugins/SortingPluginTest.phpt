<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Plugins;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Exceptions\ColumnNotSortableException;
use JuniWalk\Tests\Files\TestPresenter;
use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;

class SortingPluginTest extends TestCase
{
	public function testDefault(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');
		$table->clearRememberedState();

		Assert::count(0, $table->getDefaultSort());
		Assert::false($table->isSortMultiple());
		Assert::false($table->isSortable());

		Assert::exception(
			fn() => $table->setDefaultSort(['name' => 'asc']),
			ColumnNotSortableException::class,
		);

		$table->setSortable()->setSortMultiple();
		$table->setDefaultSort(['name' => 'desc', 'id' => 'asc']);

		Assert::true($table->isSortMultiple());
		Assert::true($table->isSortable());
		Assert::same(
			['name' => Sort::DESC, 'id' => Sort::ASC],
			$table->getDefaultSort()
		);

		Assert::with($table, function() {
			$columns = $this->getColumnsSorted();

			Assert::same(['name', 'id'], array_keys($columns));
		});
	}


	public function testHandler(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');
		$table->setDefaultSort(['id' => 'asc']);
		$table->clearRememberedState();

		Assert::same([], $table->sort);
		Assert::exception(
			fn() => $table->handleSort('id'),
			AbortException::class,
		);

		Assert::hasKey('id', $table->sort);
		Assert::same('desc', $table->sort['id']->value);
		Assert::exception(
			fn() => $table->handleSort('id'),
			AbortException::class,
		);

		Assert::same([], $table->sort);
	}


	public function testHandler_NotSortable(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');
		$table->clearRememberedState();

		Assert::exception(
			fn() => $table->handleSort('name'),
			ColumnNotSortableException::class,
		);
	}


	public function testRender(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');
		$table->setDefaultSort(['id' => 'asc']);
		$table->clearRememberedState();
		$table->setSortable(true);

		Assert::null($table->getColumn('name')->isSortable());
		Assert::null($table->getColumn('id')->isSorted());

		Assert::with($table, function() {
			$template = $this->createTemplate();
			$this->onRenderSorting($template);

			Assert::true($this->getColumn('name')->isSortable());
			Assert::same('asc', $this->getColumn('id')->isSorted()->value);
		});
	}
}

(new SortingPluginTest)->run();
