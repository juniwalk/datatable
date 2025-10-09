<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Plugins;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Enums\Option;
use JuniWalk\Tests\Files\TestPresenter;
use Tester\Assert;
use Tester\TestCase;

class SessionPluginTest extends TestCase
{
	public function testState(): void
	{
		$table = (new TestPresenter)->getComponent('table');

		Assert::false($table->isRememberState());
		Assert::with($table, function() {
			Assert::null($this->getOption(Option::IsFiltered));
		});

		$table->setRememberState(true);
		Assert::true($table->isRememberState());

		Assert::with($table, function() {
			$this->setOption(Option::IsFiltered, true);
			Assert::true($this->getOption(Option::IsFiltered));
		});

		$table->clearRememberedState();
		Assert::with($table, function() {
			Assert::null($this->getOption(Option::IsFiltered));
		});
	}
}

(new SessionPluginTest)->run();
