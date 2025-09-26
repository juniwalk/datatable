<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Sources;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filters\TextFilter;
use JuniWalk\DataTable\SourceFactory;
use JuniWalk\DataTable\Sources\ArraySource;
use Tester\Assert;
use Tester\TestCase;

class ArraySourceTest extends TestCase
{
	public function testSourceBasics(): void
	{
		$source = new ArraySource(ItemsData, 'id');
		$source->setIndeterminate(true);
		$source->setPrimaryKey('name');

		Assert::same('name', $source->getPrimaryKey());
		Assert::true($source->isIndeterminate());

		Assert::type(ArraySource::class, SourceFactory::fromModel(ItemsData));
	}


	public function testFetchItem(): void
	{
		$source = new ArraySource(ItemsData, 'id');
		$items = $source->fetchItem(3);

		Assert::notContains(ItemsData[0], $items);
		Assert::contains(ItemsData[2], $items);
		Assert::count(1, $items);

		Assert::same(0, $source->getCountOnPage());	// ? fetchItem does not update the countOnPage
		Assert::same(5, $source->getCount());
	}


	public function testFilters(): void
	{
		$column = new TextColumn('Name');
		$column->setParent(null, 'name');

		$filter = new TextFilter('Name');
		$filter->setParent(null, 'name');
		$filter->setColumns($column);
		$filter->setValue('John');

		// ? Match the exact word using custom condition
		$filter->setCondition(fn($x, $y) => strpos($x['name'], $y) !== false);

		$source = new ArraySource(ItemsData, 'id');
		$items = $source->fetchItems(['name' => $filter], [], 0, 5);

		Assert::notContains(ItemsData[4], $items);
		Assert::contains(ItemsData[0], $items);
		Assert::count(1, $items);
	}


	public function testSorting(): void
	{
		$column = new TextColumn('Name');
		$column->setSortable(true);
		$column->setSorted(Sort::ASC);

		$source = new ArraySource(ItemsData, 'id');
		$items = $source->fetchItems([], ['name' => $column], 0, 5);
		$items = array_values($items);	// ? Reindex

		Assert::same(ItemsData[2], $items[0]);
		Assert::same(ItemsData[1], $items[1]);
	}


	public function testPagination(): void
	{
		$source = new ArraySource(ItemsData, 'id');
		$items = $source->fetchItems([], [], 1, 3);

		Assert::notContains(ItemsData[0], $items);
		Assert::contains(ItemsData[3], $items);
		Assert::count(3, $items);

		Assert::same(3, $source->getCountOnPage());
		Assert::same(5, $source->getCount());
	}
}

(new ArraySourceTest)->run();
