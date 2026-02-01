<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Actions;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\DetailAction;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Table;
use JuniWalk\Tests\Files\AbstractActionCase;
use Nette\Application\AbortException;
use Nette\Utils\Html;
use Tester\Assert;

class DetailActionTest extends AbstractActionCase
{
	/** @var class-string<Action> */
	protected string $className = DetailAction::class;


	public function testAction(): void
	{
		$action = $this->createAction('btn', 'Button');

		try {
			$action->handleOpen(1);

		} catch (AbortException) {
		}

		$table = $action->lookup(Table::class);

		Assert::true($table->isItemRedraw(1));
		Assert::true($table->hasDetailAction());
		Assert::same($action, $table->getActiveDetail());	

		Assert::true($table->isControlInvalid('row-1-detail'));
		Assert::true($table->isControlInvalid('rows'));
	}


	public function testRender(): void
	{
		$row = new Row(ItemsData[0], 'id');

		$action = $this->createAction('btn', 'Button');
		$action->setTargetNewTab(true);

		$link = '/index.php?table-btn-id=1&action=default&do=table-btn-open&presenter=Test';
		$html = $action->createButton($row);

		Assert::type(Html::class, $html);
		Assert::null($html->getAttribute('target'));
		Assert::same('a', $html->getName());
		Assert::same($link, $html->getHref());

		Assert::exception(
			fn() => $action->createButton(null),
			InvalidStateException::class,
		);
	}
}

(new DetailActionTest)->run();
