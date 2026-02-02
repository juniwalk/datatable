<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Plugins;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Exceptions\SourceMissingException;
use JuniWalk\DataTable\Sources\ArraySource;
use JuniWalk\Tests\Files\TestPresenter;
use Tester\Assert;
use Tester\TestCase;

class SourcesPluginTest extends TestCase
{
	public function testEvents(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');
		$table->addLoadCallback(function() use (&$onLoad) { $onLoad = true; });
		$table->addItemCallback(function() use (&$onItem) { $onItem = true; });

		$table->render();

		Assert::true($onLoad ?? false);
		Assert::true($onItem ?? false);
	}


	public function testRedrawing(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		Assert::false($table->isItemRedraw(0));

		$table->setItemRedraw(1);
		Assert::true($table->isItemRedraw(1));
	}


	public function testExtended(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');

		Assert::type(ArraySource::class, $table->getSource());
		Assert::hasKey('name', $table->getColumns());
	}


	public function testMissingSource(): void
	{
		$table = (new TestPresenter)->getComponent('table');

		Assert::exception(
			fn() => $table->getSource(),
			SourceMissingException::class,
		);

		Assert::exception(
			fn() => $table->render(),
			SourceMissingException::class,
		);
	}
}

(new SourcesPluginTest)->run();
