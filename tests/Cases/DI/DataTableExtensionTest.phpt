<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\DI;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\DI\DataTableExtension;
use Tester\Assert;
use Tester\TestCase;

class DataTableExtensionTest extends TestCase
{
	public function testGetTranslationResources(): void
	{
		$extension = new DataTableExtension;
		$resources = $extension->getTranslationResources();

		Assert::count(1, $resources);
		Assert::true(is_dir($resources[0]));
	}
}

(new DataTableExtensionTest)->run();
