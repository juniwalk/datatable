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
use JuniWalk\Tests\Files\AbstractColumnCase;
use Tester\Assert;

class EnumColumnTest extends AbstractColumnCase
{
	/** @var class-string<Column> */
	protected string $className = Columns\EnumColumn::class;


	public function testColumn(): void
	{
		$column = $this->createColumn('align', 'Align');

		Assert::with($column, function() {
			$row = new Row(ItemsData[0], 'id');
			$html = $this->formatValue($row);

			Assert::same(Align::Left->value, trim($html->getText()));
		});
	}
}

(new EnumColumnTest)->run();
