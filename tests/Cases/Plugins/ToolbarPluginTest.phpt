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

class ToolbarPluginTest extends TestCase
{
	public function testActions(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->addToolbarLink('link', 'Link');
		$table->addToolbarButton('button', 'Button');
		$table->addToolbarDropdown('dropdown', 'Dropdown');
		$table->addToolbarCallback('callback', 'Callback');

		Assert::type(Actions\LinkAction::class, $table->getToolbarAction('link'));
		Assert::type(Actions\ButtonAction::class, $table->getToolbarAction('button'));
		Assert::type(Actions\DropdownAction::class, $table->getToolbarAction('dropdown'));
		Assert::type(Actions\CallbackAction::class, $table->getToolbarAction('callback'));

		$table->removeToolbarAction('button');
		$actions = $table->getToolbarActions();

		Assert::hasNotKey('button', $actions);
		Assert::null($table->getToolbarAction('button', false));
		Assert::exception(
			fn() => $table->getToolbarAction('button'),
			ActionNotFoundException::class,
		);
	}


	public function testCondition(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->addToolbarCallback('callback', 'Callback');
		$table->allowToolbarAction('callback', false);

		Assert::false($table->getToolbarAction('callback')->isAllowed());
		Assert::exception(
			fn() => $table->allowToolbarAction('link', false),
			ActionNotFoundException::class,
		);
	}


	public function testRender(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->addToolbarLink('link', 'Link', 'grpA');
		$table->addToolbarButton('button', 'Button', 'grpA');
		$table->addToolbarDropdown('dropdown', 'Dropdown', 'grpB');
		$table->addToolbarCallback('callback', 'Callback', 'grpC');

		$table->allowToolbarAction('button', false);

		Assert::with($table, function() {
			$template = $this->createTemplate();
			$this->onRenderToolbar($template);

			$actions = $template->toolbar ?? null;

			Assert::type('array', $actions);
			Assert::count(3, $actions);

			Assert::same(['grpA', 'grpB', 'grpC'], array_keys($actions));
			Assert::hasNotKey('button', $actions['grpA']);
			Assert::hasKey('link', $actions['grpA']);
			Assert::hasKey('dropdown', $actions['grpB']);
			Assert::hasKey('callback', $actions['grpC']);
		});
	}
}

(new ToolbarPluginTest)->run();
