<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Filters;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Columns;
use JuniWalk\DataTable\Enums\Status;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;
use JuniWalk\Tests\Files\AbstractColumnCase;
use JuniWalk\Tests\Files\Enums\BinaryNoIcon;
use JuniWalk\Tests\Files\Enums\WordsOnly;
use Nette\Utils\Helpers;
use Tester\Assert;
use ValueError;

class StatusColumnTest extends AbstractColumnCase
{
	/** @var class-string<Column> */
	protected string $className = Columns\StatusColumn::class;


	public function testColumn_Badge_Default(): void
	{
		$column = $this->createColumn('status', 'Status');
		/** @var Columns\StatusColumn $column */
		$row = new Row(['id' => 1, 'status' => true], 'id');

		Assert::same(Status::Badge, $column->getFormat());

		$html = Helpers::capture(fn() => $column->render($row));

		Assert::contains('badge', $html);
		Assert::contains('fa-check', $html);
		Assert::contains('web.general.yes', $html);
	}


	public function testColumn_Label_Format(): void
	{
		$column = $this->createColumn('status', 'Status');
		/** @var Columns\StatusColumn $column */
		$column->setFormat('label');

		$row = new Row(['id' => 1, 'status' => false], 'id');

		Assert::same(Status::Label, $column->getFormat());

		$html = Helpers::capture(fn() => $column->render($row));

		Assert::contains('<strong', $html);
		Assert::contains('text-danger', $html);
		Assert::contains('web.general.no', $html);
	}


	public function testColumn_Icon_Format(): void
	{
		$column = $this->createColumn('status', 'Status');
		/** @var Columns\StatusColumn $column */
		$column->setFormat(Status::Icon);

		$row = new Row(['id' => 1, 'status' => true], 'id');

		Assert::same(Status::Icon, $column->getFormat());

		$html = Helpers::capture(fn() => $column->render($row));

		Assert::contains('<i', $html);
		Assert::contains('fa-check', $html);
		Assert::contains('text-success', $html);
		Assert::contains('title="web.general.yes"', $html);
		Assert::contains('data-bs-toggle="tooltip"', $html);
	}


	public function testColumn_Icon_Format_Fallback_To_Badge(): void
	{
		$column = $this->createColumn('status', 'Status');
		/** @var Columns\StatusColumn $column */
		$column->setEnum(BinaryNoIcon::class);
		$column->setFormat(Status::Icon);

		$row = new Row(['id' => 1, 'status' => true], 'id');
		$html = Helpers::capture(fn() => $column->render($row));

		Assert::same(Status::Badge, $column->getFormat());
		Assert::contains('badge', $html);
		Assert::false(str_contains($html, 'fa-'));
	}


	public function testColumn_Setters(): void
	{
		$column = $this->createColumn('status', 'Status');
		/** @var Columns\StatusColumn $column */

		$column->setFormat('label');
		$column->setEnum(BinaryNoIcon::class);

		Assert::same(Status::Label, $column->getFormat());
		Assert::same(BinaryNoIcon::class, $column->getEnum());

		Assert::exception(
			fn() => $column->setFormat('invalid'),
			ValueError::class,
		);
	}


	public function testColumn_Invalid_Value(): void
	{
		$column = $this->createColumn('status', 'Status');
		/** @var Columns\StatusColumn $column */
		$column->setEnum(WordsOnly::class);

		$row = new Row(['id' => 1, 'status' => 'definitely-active'], 'id');

		Assert::exception(
			fn() => $column->render($row),
			FieldInvalidException::class,
		);
	}
}

(new StatusColumnTest)->run();
