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

class NumberColumnTest extends AbstractColumnCase
{
	/** @var class-string<Column> */
	protected string $className = Columns\NumberColumn::class;


	public function testColumn(): void
	{
		$column = $this->createColumn('height', 'Height');
		$column->setFormat(0, ',');

		Assert::with($column, function() {
			$row = new Row(ItemsData[0], 'id');

			Assert::same('187', $this->formatValue($row));
			Assert::same(',', $this->getSeparator());
			Assert::same(0, $this->getPrecision());
		});
	}
}

(new NumberColumnTest)->run();
