<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use JuniWalk\Tests\Files\TestPresenter;
use Tester\Assert;
use Tester\TestCase;

class TableTest extends TestCase
{
	public function testRender(): void
	{
		$presenter = new TestPresenter;

		$table = $presenter->getComponent('table');
		$table->setCaption('Table Caption');

		$table->when('load', function() use (&$onLoad) { $onLoad = true; });
		$table->when('item', function() use (&$onItem) { $onItem = true; });
		$table->when('render', function() use (&$onRender) { $onRender = true; });

		$table->render();

		Assert::same('Table Caption', $table->getCaption());
		Assert::true($onLoad ?? false);
		Assert::true($onItem ?? false);
		Assert::true($onRender ?? false);
	}
}

(new TableTest)->run();
