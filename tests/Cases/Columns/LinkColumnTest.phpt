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

	protected const Link = '/index.php?table-link-name=John+Doe&table-link-id=1&action=default&presenter=Test';


	public function testColumn(): void
	{
		$column = $this->createColumn('link', 'Link');
		$column->setLink('this', ['name' => '@name']);

		$row = new Row(ItemsData[0], 'id');

		Assert::with($column, function() use ($row) {
			$html = $this->formatValue($row);
			Assert::same(LinkColumnTest::Link, $html->getHref());
		});
	}


	public function testRender_Callback(): void
	{
		$column = $this->createColumn('link', 'Link');
		$column->setLink('this', ['name' => '@name']);

		$row = new Row(ItemsData[0], 'id');

		$output = Helpers::capture(function() use ($column, $row) {
			$column->setRenderer(fn($item, $html) => $html->setText('Jane Doe'));
			$column->render($row);
		});

		$html = '<a href="'.str_replace('&', '&amp;', LinkColumnTest::Link).'" class="fw-bold">Jane Doe</a>';
		Assert::same($html, $output);
	}
}

(new LinkColumnTest)->run();
