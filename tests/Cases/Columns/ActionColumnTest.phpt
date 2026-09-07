<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Columns;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Actions\ButtonAction;
use JuniWalk\DataTable\Columns;
use JuniWalk\DataTable\Row;
use JuniWalk\Tests\Files\AbstractColumnCase;
use Nette\Utils\Helpers;
use Nette\Utils\Html;
use Tester\Assert;

class ActionColumnTest extends AbstractColumnCase
{
	/** @var class-string<Columns\AbstractColumn> */
	protected string $className = Columns\ActionColumn::class;


	public function testColumn(): void
	{
		$column = $this->createColumn('actions', 'Actions');

		$action1 = new ButtonAction('Edit');
		$action1->setParent(null, 'edit');

		$action2 = new ButtonAction('Delete');
		$action2->setParent(null, 'delete');
		$action2->setAllowCondition(false);

		$column->addActions([
			'edit' => $action1,
			'delete' => $action2,
		]);

		Assert::with($column, function() {
			$row = new Row(ItemsData[0], 'id');

			$toolbar = $this->formatValue($row);
			Assert::type(Html::class, $toolbar);

			$output = Helpers::capture(fn() => $this->render($row));
			Assert::contains('Edit', $output);
			Assert::notContains('Delete', $output); // Allowed condition is false
		});
	}
}

(new ActionColumnTest)->run();
