<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Plugins;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Enums\Option;
use JuniWalk\Tests\Files\Reflect;
use JuniWalk\Tests\Files\TestPresenter;
use Tester\Assert;
use Tester\TestCase;

class SessionPluginTest extends TestCase
{
	public function testState(): void
	{
		$table = (new TestPresenter)->getComponent('table');

		$setOption = Reflect::closure($table, 'setOption');
		$getOption = Reflect::closure($table, 'getOption');

		Assert::false($table->isRememberState());
		Assert::null($getOption(Option::IsFiltered));

		$table->setRememberState(true);
		Assert::true($table->isRememberState());

		$setOption(Option::IsFiltered, true);
		Assert::true($getOption(Option::IsFiltered));

		$table->clearRememberedState();
		Assert::null($getOption(Option::IsFiltered));
	}
}

(new SessionPluginTest)->run();
