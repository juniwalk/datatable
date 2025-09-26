<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Actions;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\LinkAction;
use JuniWalk\Tests\Files\AbstractActionCase;
use Nette\Utils\Html;
use Tester\Assert;

class LinkActionTest extends AbstractActionCase
{
	/** @var class-string<Action> */
	protected string $className = LinkAction::class;


	public function testRender(): void
	{
		$action = $this->createAction('link', 'link');
		$action->setLink('this');

		$link = '/index.php?action=default&presenter=Test';
		$html = $action->createButton(null);

		Assert::type(Html::class, $html);
		Assert::same('a', $html->getName());
		Assert::same($link, $html->getHref());
	}
}

(new LinkActionTest)->run();
