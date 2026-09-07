<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Sources;

require __DIR__ . '/../../bootstrap.php';

use Doctrine\DBAL\DriverManager;
use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filters\TextFilter;
use JuniWalk\DataTable\SourceFactory;
use JuniWalk\DataTable\Sources\DoctrineDBALSource;
use Tester\Assert;
use Tester\TestCase;

class DoctrineDBALSourceTest extends TestCase
{
	private DoctrineDBALSource $source;


	public function setUp(): void
	{
		$conn = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
		$conn->executeStatement('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
		$conn->insert('users', ['id' => 1, 'name' => 'John']);
		$conn->insert('users', ['id' => 2, 'name' => 'Jane']);
		$conn->insert('users', ['id' => 3, 'name' => 'Jack']);

		$qb = $conn->createQueryBuilder()->select('*')->from('users');
		$this->source = new DoctrineDBALSource($qb, 'id');
	}


	public function testBasics(): void
	{
		Assert::same('id', $this->source->getPrimaryKey());
		Assert::same(3, $this->source->getcount());

		$conn = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
		$qb = $conn->createQueryBuilder()->select('*')->from('users');
		Assert::type(DoctrineDBALSource::class, SourceFactory::fromModel($qb));
	}


	public function testSortingAndPagination(): void
	{
		$column = new TextColumn('Name');
		$column->setSortable(true);
		$column->setSorted(Sort::ASC);

		$items = $this->source->fetchItems([], ['name' => $column], 0, 2);

		Assert::count(2, $items);
		Assert::same('Jack', $items[0]['name']);
		Assert::same('Jane', $items[1]['name']);
	}


	public function testFilter(): void
	{
		$column = new TextColumn('Name');
		$column->setParent(null, 'name');

		$filter = new TextFilter('Name');
		$filter->setParent(null, 'name');
		$filter->setColumns($column);
		$filter->setValue('John');

		$items = $this->source->fetchItems(['name' => $filter], [], 0, 10);

		Assert::count(1, $items);
		Assert::same('John', $items[0]['name']);
	}
}

(new DoctrineDBALSourceTest)->run();
