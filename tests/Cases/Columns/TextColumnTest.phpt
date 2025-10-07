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
use Tester\Assert;

class TextColumnTest extends AbstractColumnCase
{
	/** @var class-string<Column> */
	protected string $className = Columns\TextColumn::class;


	public function testColumn(): void
	{
		$column = $this->createColumn('name', 'Name');
		$column->setTruncate(5);

		Assert::with($column, function() {
			$row = new Row(ItemsData[0], 'id');

			Assert::same('John…', $this->formatValue($row));
			Assert::same(5, $this->getTruncate());
		});
	}
}

(new TextColumnTest)->run();
