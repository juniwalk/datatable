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

class DateColumnTest extends AbstractColumnCase
{
	/** @var class-string<Column> */
	protected string $className = Columns\DateColumn::class;


	public function testColumn(): void
	{
		$column = $this->createColumn('birth', 'Birth');
		$column->setFormat('Y-m-d');

		Assert::with($column, function() {
			$row = new Row(ItemsData[0], 'id');

			Assert::same('1990-12-16', $this->formatValue($row));
			Assert::same('Y-m-d', $this->getFormat());
		});
	}
}

(new DateColumnTest)->run();
