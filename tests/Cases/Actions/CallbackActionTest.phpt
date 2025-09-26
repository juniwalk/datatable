<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Actions;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\CallbackAction;
use JuniWalk\Tests\Files\AbstractActionCase;
use Nette\Application\AbortException;
use Nette\Utils\Html;
use Tester\Assert;

class CallbackActionTest extends AbstractActionCase
{
	/** @var class-string<Action> */
	protected string $className = CallbackAction::class;


	public function testAction(): void
	{
		$action = $this->createAction('btn', 'Button');
		$action->addClickCallback(function() use (&$isClicked) {
			$isClicked = true;
		});

		try {
			$action->handleAction(1);

		} catch (AbortException) {
		}

		Assert::true($isClicked);
	}


	public function testRender(): void
	{
		$action = $this->createAction('btn', 'Button');

		$link = '/index.php?action=default&do=table-btn-action&presenter=Test';
		$html = $action->createButton(null);

		Assert::type(Html::class, $html);
		Assert::same('a', $html->getName());
		Assert::same($link, $html->getHref());
	}
}

(new CallbackActionTest)->run();
