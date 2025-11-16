<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Filters;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Columns;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;
use JuniWalk\Tests\Files\AbstractColumnCase;
use Nette\Utils\Helpers;
use Tester\Assert;

class OrderColumnTest extends AbstractColumnCase
{
	/** @var class-string<Column> */
	protected string $className = Columns\OrderColumn::class;


	public function testColumn(): void
	{
		$column = $this->createColumn('order', 'Order');
		$row = new Row(ItemsData[0], 'id');

		Assert::false($column->isDisabled());
		Assert::with($column, function() use ($row) {
			$btn = $this->formatValue($row);
			Assert::same('true', $btn->getAttribute('data-dt-sort'));
		});

		$column->setDisabled(true);

		Assert::true($column->isDisabled());
		Assert::with($column, function() use ($row) {
			$btn = $this->formatValue($row);
			Assert::hasKey('disabled', $btn->getClass());
		});
	}


	public function testColumn_Invalid_Value(): void
	{
		$column = $this->createColumn('birth', 'Birth');
		$row = new Row(ItemsData[0], 'id');

		Assert::exception(
			fn() => $column->render($row),
			FieldInvalidException::class,
		);
	}
}

(new OrderColumnTest)->run();
