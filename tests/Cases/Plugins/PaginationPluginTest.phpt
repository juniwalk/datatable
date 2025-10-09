<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Plugins;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\Tests\Files\TestPresenter;
use Nette\Application\AbortException;
use Nette\Utils\Paginator;
use Tester\Assert;
use Tester\TestCase;

class PaginationPluginTest extends TestCase
{
	public function testPage(): void
	{
		$table = (new TestPresenter)->getComponent('table');

		Assert::same(1, $table->getPage());
		Assert::exception(
			fn() => $table->handlePage(5),
			AbortException::class,
		);

		$table->setPage(-1);
		Assert::same(1, $table->getPage());
	}


	public function testLimit(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->setLimits([10, 50, 20], true);

		Assert::same([10, 20, 50, 0], $table->getLimits());
		Assert::same(10, $table->getCurrentLimit());
		Assert::same(0, $table->getOffset());

		Assert::exception(
			fn() => $table->handleLimit(20),
			AbortException::class,
		);
	}


	public function testLimit_Default(): void
	{
		$table = (new TestPresenter)->getComponent('table');

		Assert::null($table->limit);
		Assert::null($table->getLimitDefault());
		Assert::true($table->isLimitDefault());

		$table->setDefaultLimit(20);
		$table->limit = 10;

		Assert::same(10, $table->getCurrentLimit());
		Assert::false($table->isLimitDefault());
	}


	public function testLimit_Invalid(): void
	{
		$table = (new TestPresenter)->getComponent('table');

		Assert::exception(
			fn() => $table->setLimits([], true),
			InvalidStateException::class,
		);

		Assert::exception(
			fn() => $table->setDefaultLimit(100),
			InvalidStateException::class,
		);
	}


	// public function testRender_Pages(): void
	// {
	// 	$table = (new TestPresenter)->getComponent('tableWithSource');
	// 	$table->renderPages();
	// }


	// public function testRender_Limiter(): void
	// {
	// 	$table = (new TestPresenter)->getComponent('tableWithSource');
	// 	$table->renderLimiter();
	// }


	public function testSteps(): void
	{
		$table = (new TestPresenter)->getComponent('table');

		$pages = new Paginator;
		$pages->setItemsPerPage(5);
		$pages->setItemCount(100);
		$pages->setPage(1);

		Assert::with($table, function() use ($pages) {
			Assert::same(
				[1, 2, 3, 4, null, 20],
				$this->createSteps($pages, 5),
			);
		});
	}
}

(new PaginationPluginTest)->run();
