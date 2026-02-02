<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Plugins;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Exceptions\ColumnNotFoundException;
use JuniWalk\DataTable\Exceptions\ColumnSortRequiredException;
use JuniWalk\Tests\Files\TestPresenter;
use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;

class OrderingPluginTest extends TestCase
{
	private const Delta = [
		1 => '-1',
		2 => '0',
		3 => '1',
	];


	public function testHandler(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');
		$table->addOrderCallback(function(array $items, array $delta) {
			Assert::same(array_keys($delta), array_column($items, 'id'));
			Assert::same(actual: $delta, expected: [
				1 => -1,
				3 => 1,
			]);
		});

		Assert::exception(
			// ! Not being sorted by the order column
			fn() => $table->handleOrdering(static::Delta),
			ColumnSortRequiredException::class,
		);

		$table->setDefaultSort(['order' => 'asc', 'id' => 'desc']);
		$table->clearRememberedState();

		Assert::exception(
			// ! Sorted by the order column but not exclusively
			fn() => $table->handleOrdering(static::Delta),
			ColumnSortRequiredException::class,
		);

		$table->setDefaultSort(['order' => 'asc']);
		$table->clearRememberedState();

		Assert::exception(
			// ? All should be right for ordering rows
			fn() => $table->handleOrdering(static::Delta),
			AbortException::class,
		);
	}


	public function testHandler_Missing_OrderColumn(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->clearRememberedState();

		Assert::exception(
			// ! Missing OrderColumn in the table
			fn() => $table->handleOrdering(static::Delta),
			ColumnNotFoundException::class,
		);
	}


	public function testRender(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');
		$table->setDefaultSort(['order' => 'asc']);
		$table->clearRememberedState();

		Assert::with($table, function() {
			$template = $this->createTemplate();
			$this->onRenderOrdering($template);

			Assert::true($template->attributes['data-dt-allow-ordering'] ?? null);
			Assert::type('string', $template->signalOrdering ?? null);
			Assert::false($this->findOrderColumn()->isDisabled());
		});
	}


	public function testRender_Missing_OrderColumn(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->clearRememberedState();

		Assert::with($table, function() {
			$template = $this->createTemplate();
			$this->onRenderOrdering($template);

			Assert::null($template->attributes['data-dt-allow-ordering'] ?? null);
			Assert::null($template->signalOrdering ?? null);
		});
	}
}

(new OrderingPluginTest)->run();
