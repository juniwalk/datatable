<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use JuniWalk\DataTable\Exceptions\SourceMissingException;
use JuniWalk\DataTable\Tools\Output;
use JuniWalk\Tests\Files\TestPresenter;
use Tester\Assert;
use Tester\TestCase;

class TableTest extends TestCase
{
	public function testRender(): void
	{
		static $caption = 'Table Caption';

		$table = (new TestPresenter)->getComponent('tableTest');
		$table->when('render', function() use (&$onRender) { $onRender = true; });
		$table->setCaption($caption);

		$output = Output::capture(fn() => $table->render());

		Assert::same($caption, $table->getCaption());
		Assert::true($onRender ?? false);

		// ? Check that rendered output contains some key elements
		Assert::contains('id="snippet-tableTest-filters"', $output);
		Assert::contains('id="snippet-tableTest-paginator"', $output);
		Assert::contains($caption, $output);
	}


	public function testRender_Missing_Source(): void
	{
		$table = (new TestPresenter)->getComponent('table');

		Assert::exception(
			fn() => $table->render(),
			SourceMissingException::class
		);
	}


	public function testRender_Filters_Disabled(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');
		$table->setFiltering(false);

		$output = Output::capture(fn() => $table->render());

		// ? Check that rendered output contains some key elements
		Assert::notContains('id="snippet-tableTest-filters"', $output);
	}


	public function testRender_Pagination_Disabled(): void
	{
		$table = (new TestPresenter)->getComponent('tableTest');
		$table->setPagination(false);

		$output = Output::capture(fn() => $table->render());

		// ? Check that rendered output contains some key elements
		Assert::notContains('id="snippet-tableTest-paginator"', $output);
	}
}

(new TableTest)->run();
