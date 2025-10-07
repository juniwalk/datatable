<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Filters;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Columns;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Tools\Option;
use JuniWalk\Tests\Files\AbstractColumnCase;
use Tester\Assert;

class DropdownColumnTest extends AbstractColumnCase
{
	/** @var class-string<Column> */
	protected string $className = Columns\DropdownColumn::class;


	public function testColumn(): void
	{
		$column = $this->createColumn('align', 'Align');
		$column->setOptionFactory(fn($case) => Option::fromEnum($case));
		$column->setItems(Align::cases());
		$column->setLink('this');

		Assert::with($column, function() {
			$this->createActions($this->getParent());

			$row = new Row(ItemsData[0], 'id');
			$html = $this->formatValue($row);

			Assert::same('btn-group dropdown', $html->getClass());
			Assert::same('div', $html->getName());

			[$button, $dropdown] = $html->getChildren();

			Assert::same('button', $button->getName());
			Assert::same('div', $dropdown->getName());
		});
	}
}

(new DropdownColumnTest)->run();
