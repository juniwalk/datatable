<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Actions;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\DropdownAction;
use JuniWalk\Tests\Files\AbstractActionCase;
use Nette\Utils\Html;
use Tester\Assert;

class DropdownActionTest extends AbstractActionCase
{
	/** @var class-string<Action> */
	protected string $className = DropdownAction::class;


	public function testAction(): void
	{
		$action = $this->createAction('btn', 'Button');
		$actions = $action->getActions();

		Assert::hasKey('link', $actions);
		Assert::hasKey('btn_divider0', $actions);
		Assert::hasKey('click', $actions);

		$action->removeAction('click');
		$actions = $action->getActions();

		Assert::hasNotKey('click', $actions);
	}


	public function testRender(): void
	{
		$action = $this->createAction('btn', 'Button');

		$html = $action->createButton(null);

		Assert::type(Html::class, $html);
		Assert::same('btn-group dropdown', $html->getClass());
		Assert::same('div', $html->getName());

		[$button, $dropdown] = $html->getChildren();

		Assert::same('button', $button->getName());
		Assert::same('div', $dropdown->getName());

		$actions = $dropdown->getChildren();

		Assert::same('a', $actions[0]->getName());
		Assert::same('div', $actions[1]->getName());
		Assert::same('a', $actions[2]->getName());
	}


	/**
	 * @param  mixed[] $args
	 * @throws InvalidArgumentException
	 */
	protected function createAction(string $name, string $label, array $args = []): Action
	{
		$action = parent::createAction($name, $label, $args);
		$action->addActionLink('link', 'Link')->setLink('this');
		$action->addDivider();
		$action->addActionCallback('click', 'Click');

		return $action;
	}
}

(new DropdownActionTest)->run();
