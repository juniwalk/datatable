<?php declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Row;
use Tester\Assert;
use Tester\TestCase;

class RowTest extends TestCase
{
	private const array ItemData = [
		'id' => 1,
		'name' => 'John Doe',
		'height' => 186.5,
	];


	public function testFieldNotFound(): void
	{
		Assert::exception(
			fn() => new Row(self::ItemData, 'age'),
			FieldNotFoundException::class,
		);
	}


	public function testFieldInvalid(): void
	{
		Assert::exception(
			fn() => new Row(self::ItemData, 'height'),
			FieldInvalidException::class,
		);
	}


	public function testArray(): void
	{
		$row = new Row(self::ItemData, 'id');

		Assert::same(1, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
	}


	public function testObject(): void
	{
		$row = new Row((object) self::ItemData, 'id');

		Assert::same(1, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
	}


	public function testClass(): void
	{
		$item = new class(... self::ItemData) {
			public function __construct(
				private int $id,
				private string $name,
				private float $height,
			) {
			}

			public function getId(): int
			{
				return $this->id;
			}

			public function getName(): string
			{
				return $this->name;
			}
		};

		$row = new Row($item, 'id');

		Assert::same(1, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
	}
}

(new RowTest())->run();
