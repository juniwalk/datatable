<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filters\TextFilter;
use JuniWalk\DataTable\Sources\ArraySource;
use Tester\Assert;
use Tester\TestCase;

class ArraySourceTest extends TestCase
{
	private const array ItemsData = [
		['id' => 1, 'name' => 'John Doe', 'height' => 186.5],
		['id' => 2, 'name' => 'Jane Doe', 'height' => 172.3],
		['id' => 3, 'name' => 'Jack Doe', 'height' => 191.4],
		['id' => 4, 'name' => 'Jenna Doe', 'height' => 167.9],
		['id' => 5, 'name' => 'Jimmy Doe', 'height' => 178.6],
	];


	public function testSourceBasics(): void
	{
		$source = new ArraySource(static::ItemsData, 'id');
		$source->setIndeterminate(true);
		$source->setPrimaryKey('name');

		Assert::same('name', $source->getPrimaryKey());
		Assert::true($source->isIndeterminate());
	}


	public function testFetchItem(): void
	{
		$source = new ArraySource(static::ItemsData, 'id');
		$items = $source->fetchItem(3);

		Assert::notContains(static::ItemsData[0], $items);
		Assert::contains(static::ItemsData[2], $items);
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

		$source = new ArraySource(static::ItemsData, 'id');
		$items = $source->fetchItems(['name' => $filter], [], 0, 5);

		Assert::notContains(static::ItemsData[4], $items);
		Assert::contains(static::ItemsData[0], $items);
		Assert::count(1, $items);
	}


	public function testSorting(): void
	{
		$column = new TextColumn('Name');
		$column->setSortable(true);
		$column->setSorted(Sort::ASC);

		$source = new ArraySource(static::ItemsData, 'id');
		$items = $source->fetchItems([], ['name' => $column], 0, 5);
		$items = array_values($items);	// ? Reindex

		Assert::same(static::ItemsData[2], $items[0]);
		Assert::same(static::ItemsData[1], $items[1]);
	}


	public function testPagination(): void
	{
		$source = new ArraySource(static::ItemsData, 'id');
		$items = $source->fetchItems([], [], 1, 3);

		Assert::notContains(static::ItemsData[0], $items);
		Assert::contains(static::ItemsData[3], $items);
		Assert::count(3, $items);

		Assert::same(3, $source->getCountOnPage());
		Assert::same(5, $source->getCount());
	}
}

(new ArraySourceTest)->run();
