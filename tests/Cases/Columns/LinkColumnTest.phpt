<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Filters;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Columns;
use JuniWalk\DataTable\Row;
use JuniWalk\Tests\Files\AbstractColumnCase;
use Nette\Utils\Helpers;
use Tester\Assert;

class LinkColumnTest extends AbstractColumnCase
{
	/** @var class-string<Column> */
	protected string $className = Columns\LinkColumn::class;

	protected const Link = '/index.php?table-name-align=left&table-name-id=1&action=default&presenter=Test';


	public function testColumn(): void
	{
		$column = $this->createColumn('name', 'Name');
		$column->setLink('this', ['align' => '@align']);

		$row = new Row(ItemsData[0], 'id');

		Assert::with($column, function() use ($row) {
			Assert::same(LinkColumnTest::Link, $this->formatValue($row)->getHref());
		});

		$output = Helpers::capture(function() use ($column, $row) {
			$column->render($row);
		});

		$html = '<a class="fw-bold" href="'.str_replace('&', '&amp;', LinkColumnTest::Link).'">John Doe</a>';
		Assert::same($html, $output);
	}


	public function testRender_Callback(): void
	{
		$column = $this->createColumn('name', 'Name');
		$column->setLink('this', ['align' => '@align']);

		$row = new Row(ItemsData[0], 'id');

		$output = Helpers::capture(function() use ($column, $row) {
			$column->setRenderer(fn($item, $html) => $html->setText('Jane Doe'));
			$column->render($row);
		});

		$html = '<a class="fw-bold" href="'.str_replace('&', '&amp;', LinkColumnTest::Link).'">Jane Doe</a>';
		Assert::same($html, $output);
	}
}

(new LinkColumnTest)->run();
