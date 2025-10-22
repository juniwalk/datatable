<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Tools;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Tools\FormatName;
use Tester\Assert;
use Tester\TestCase;

class FormatNameTest extends TestCase
{
	public function testComponent(): void
	{
		Assert::same('signal', FormatName::component('signal!'));
		Assert::same('php84', FormatName::component('php8.4'));
		Assert::same('isActive', FormatName::component('isActive'));
		Assert::same('structName', FormatName::component('struct-name'));
	}
}

(new FormatNameTest)->run();
