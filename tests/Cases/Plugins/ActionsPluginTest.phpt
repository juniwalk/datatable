<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Plugins;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Actions;
use JuniWalk\DataTable\Exceptions\ActionNotFoundException;
use JuniWalk\Tests\Files\TestPresenter;
use Tester\Assert;
use Tester\TestCase;

class ActionsPluginTest extends TestCase
{
	public function testActions(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->addActionLink('link', 'Link');
		$table->addActionDropdown('dropdown', 'Dropdown');
		$table->addActionCallback('callback', 'Callback');
		$table->addActionDetail('detail', 'Detail');

		Assert::type(Actions\LinkAction::class, $table->getAction('link'));
		Assert::type(Actions\DropdownAction::class, $table->getAction('dropdown'));
		Assert::type(Actions\CallbackAction::class, $table->getAction('callback'));
		Assert::type(Actions\DetailAction::class, $table->getAction('detail'));

		$table->removeAction('detail');
		$actions = $table->getActions();

		Assert::hasNotKey('detail', $actions);
		Assert::null($table->getAction('detail', false));
		Assert::exception(
			fn() => $table->getAction('detail'),
			ActionNotFoundException::class,
		);
	}


	public function testCondition(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->addActionCallback('callback', 'Callback');
		$table->allowRowAction('callback', false);

		Assert::false($table->getAction('callback')->isAllowed());
		Assert::exception(
			fn() => $table->allowRowAction('detail', false),
			ActionNotFoundException::class,
		);
	}


	public function testDetail(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		Assert::false($table->hasDetailAction());

		$table->addActionDetail('detail', 'Detail');
		Assert::true($table->hasDetailAction());
		Assert::null($table->getActiveDetail());

		$table->setActiveDetail($table->getAction('detail'));
		$action = $table->getActiveDetail();

		Assert::type(Actions\DetailAction::class, $action);
		Assert::same('detail', $action->getName());

		$table->removeAction('detail');
		Assert::false($table->hasDetailAction());
	}
}

(new ActionsPluginTest)->run();
