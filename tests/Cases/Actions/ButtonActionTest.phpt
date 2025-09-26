<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Actions;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\ButtonAction;
use JuniWalk\Tests\Files\AbstractActionCase;
use Nette\Utils\Html;
use Tester\Assert;

class ButtonActionTest extends AbstractActionCase
{
	/** @var class-string<Action> */
	protected string $className = ButtonAction::class;


	public function testRender(): void
	{
		$action = $this->createAction('btn', 'Button');

		$html = $action->createButton(null);

		Assert::type(Html::class, $html);
		Assert::same('button', $html->getName());
	}
}

(new ButtonActionTest)->run();
