<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Row;
use Tester\Assert;
use Tester\TestCase;

class RowTest extends TestCase
{
	public function testFieldNotFound(): void
	{
		Assert::exception(
			fn() => new Row(ItemsData[0], 'age'),
			FieldNotFoundException::class,
		);
	}


	public function testFieldInvalid(): void
	{
		Assert::exception(
			fn() => new Row(ItemsData[0], 'height'),
			FieldInvalidException::class,
		);
	}


	public function testArray(): void
	{
		$row = new Row(ItemsData[0], 'id');

		Assert::same(1, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
	}


	public function testObject(): void
	{
		$row = new Row((object) ItemsData[0], 'id');

		Assert::same(1, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
	}


	public function testClass(): void
	{
		$item = new class(... ItemsData[0]) {
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

(new RowTest)->run();
