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
		$table = (new TestPresenter)->getComponent('tableWithSource');
		$table->when('render', function() use (&$onRender) { $onRender = true; });
		$table->setCaption('Table Caption');

		$table->render();

		Assert::same('Table Caption', $table->getCaption());
		Assert::true($onRender ?? false);
	}
}

(new TableTest)->run();
