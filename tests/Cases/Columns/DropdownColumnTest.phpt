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


	public function testColumn_Disabled(): void
	{
		$column = $this->createColumn('align', 'Align');
		$column->setItems(Align::cases());
		$column->setLink('this');

		Assert::with($column, function() {
			$this->createActions($this->getParent());
			$this->setDisabled(true);

			$row = new Row(ItemsData[0], 'id');
			$html = $this->formatValue($row);

			Assert::same(['badge' => true, 'text-bg-secondary' => true, 'badge-secondary' => true], $html->getClass());
			Assert::same('span', $html->getName());
		});
	}


	public function testColumn_Callback(): void
	{
		$column = $this->createColumn('align', 'Align');
		$column->setItems(Align::cases());
		$column->setLink('this');

		$column->setActionCallback(function(Option $opt, $row) {
			return $opt->value !== Align::Right->value;
		});

		Assert::with($column, function() {
			$this->createActions($this->getParent());

			$row = new Row(ItemsData[0], 'id');
			$html = $this->formatValue($row);

			$btn = $html->getChildren()[0];

			Assert::same(['btn btn-xs btn-secondary' => true, 'dropdown-toggle' => true], $btn->getClass());
			Assert::same('button', $btn->getName());

			$actions = $html->getChildren()[1]->getChildren();

			foreach ($actions as $link) {
				if ($link->getText() !== Align::Right->value) {
					continue;
				}

				Assert::hasKey('disabled', $link->getClass());
			}
		});
	}
}

(new DropdownColumnTest)->run();
