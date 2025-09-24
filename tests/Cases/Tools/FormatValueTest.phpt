<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Enums\Option;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Tools\FormatValue;
use Tester\Assert;
use Tester\TestCase;

class FormatValueTest extends TestCase
{
	public function testString(): void
	{
		Assert::type('string', FormatValue::string(1260));
		Assert::type('string', FormatValue::string(null));
	}


	public function testNumber(): void
	{
		Assert::type('int', FormatValue::number('1250'));
		Assert::type('float', FormatValue::number('12.50'));
		Assert::type('float', FormatValue::number('12,50'));
		Assert::type('null', FormatValue::number('nan'));

		Assert::same(12.5, FormatValue::number('12.53', 1));
	}


	public function testEnum(): void
	{
		Assert::same(Option::StateLimit, FormatValue::enum('limitState', Option::class));
		Assert::same(Sort::ASC, FormatValue::enum('asc', Sort::class));
		Assert::null(FormatValue::enum('', Sort::class));

		Assert::exception(
			fn() => FormatValue::enum('none', Option::class),
			ValueError::class,
		);
	}


	public function testDateTime(): void
	{
		Assert::type(DateTimeImmutable::class, FormatValue::datetime('1. 1. 2000 0:00', ['j. n. Y G:i']));
		Assert::type(DateTimeImmutable::class, FormatValue::datetime('2000-01-01 00:00:00'));
		Assert::type(DateTimeImmutable::class, FormatValue::datetime(new DateTime));
		Assert::type(DateTimeImmutable::class, FormatValue::datetime(946684800));
		Assert::null(FormatValue::datetime(''));

		Assert::exception(
			fn() => FormatValue::datetime('not a date'),
			DateMalformedStringException::class,
		);
	}
}

(new FormatValueTest)->run();
