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
		$column->setFormat(2, ',', '.');

		Assert::with($column, function() {
			$row = new Row(ItemsData[0], 'id');

			Assert::same('186,50', $this->formatValue($row));
			Assert::same(2, $this->getDecimals());
			Assert::same(',', $this->getDecimalSeparator());
			Assert::same('.', $this->getThousandsSeparator());
		});
	}
}

(new NumberColumnTest)->run();
