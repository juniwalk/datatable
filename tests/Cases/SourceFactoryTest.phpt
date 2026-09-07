<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use JuniWalk\DataTable\Exceptions\SourceUnknownException;
use JuniWalk\DataTable\SourceFactory;
use JuniWalk\DataTable\Sources\ArraySource;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

class SourceFactoryTest extends TestCase
{
	public function testFromModel(): void
	{
		$arraySource = new ArraySource(ItemsData, 'id');
		Assert::same($arraySource, SourceFactory::fromModel($arraySource));

		$source = SourceFactory::fromModel(ItemsData);
		Assert::type(ArraySource::class, $source);
	}


	public function testUnknownSource(): void
	{
		Assert::exception(
			fn() => SourceFactory::fromModel(new stdClass),
			SourceUnknownException::class,
		);
	}
}

(new SourceFactoryTest)->run();
