<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Tools;

require __DIR__ . '/../../bootstrap.php';

use DateTime;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Tools\Compare;
use Tester\Assert;
use Tester\TestCase;

class CompareTest extends TestCase
{
	public function testString(): void
	{
		Assert::false(Compare::string('John', 'Jenna'));
		Assert::true(Compare::string('Hello', 'hello'));
	}


	public function testMatch(): void
	{
		Assert::false(Compare::match('John', 'Jenna'));
		Assert::true(Compare::match('Hello', 'hello'));
	}


	public function testEnum(): void
	{
		Assert::false(Compare::enum('asc', 'left', Sort::class));
		Assert::true(Compare::enum('asc', Sort::ASC, Sort::class));
	}


	public function testDate(): void
	{
		Assert::false(Compare::date('2025-01-01', '2024-01-01'));
		Assert::true(Compare::date('2025-05-05', new DateTime('2025-05-05')));
	}


	public function testDateTime(): void
	{
		Assert::false(Compare::datetime('2025-01-01 12:00:00', '2024-01-01 09:00:00'));
		Assert::true(Compare::datetime('2025-05-05 15:55:55', new DateTime('2025-05-05 15:55:55')));
	}
}

(new CompareTest)->run();
