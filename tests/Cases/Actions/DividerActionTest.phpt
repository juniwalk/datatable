<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Actions;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\DividerAction;
use JuniWalk\DataTable\Actions\DropdownAction;
use JuniWalk\Tests\Files\AbstractActionCase;
use Nette\Utils\Html;
use Tester\Assert;

class DividerActionTest extends AbstractActionCase
{
	/** @var class-string<Action> */
	protected string $className = DividerAction::class;


	public function testRender_Parent_None(): void
	{
		$action = $this->createAction('btn', 'Button');

		$html = $action->createButton(null);

		Assert::type(Html::class, $html);
		Assert::same('vr h-100 mx-lg-2', $html->getClass());
		Assert::same('div', $html->getName());
	}


	public function testRender_Parent_DropdownAction(): void
	{
		$action = $this->createAction('btn', 'Button');
		$action->setParent(null)->setParent(new DropdownAction('dd'));

		$html = $action->createButton(null);

		Assert::type(Html::class, $html);
		Assert::same('dropdown-divider', $html->getClass());
		Assert::same('div', $html->getName());
	}
}

(new DividerActionTest)->run();
