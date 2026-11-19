<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Sources;

require __DIR__ . '/../../bootstrap.php';

use ArrayObject;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filters\TextFilter;
use JuniWalk\DataTable\SourceFactory;
use JuniWalk\DataTable\Sources\DoctrineORMSource;
use JuniWalk\Tests\Files\PruningUser;
use JuniWalk\Tests\Files\RecordingQueryBuilder;
use ReflectionClass;
use Tester\Assert;
use Tester\TestCase;

class DoctrineORMSourceTest extends TestCase
{
	private DoctrineORMSource $source;
	private QueryBuilder $qb;


	public function setUp(): void
	{
		$em = (new ReflectionClass(EntityManager::class))->newInstanceWithoutConstructor();
		$this->qb = new QueryBuilder($em);
		$this->qb->select('u')->from('User', 'u');

		$this->source = new DoctrineORMSource($this->qb, 'id');
	}


	public function testBasics(): void
	{
		Assert::same('id', $this->source->getPrimaryKey());
		Assert::type(DoctrineORMSource::class, SourceFactory::fromModel($this->qb));
	}


	public function testSorting(): void
	{
		$column = new TextColumn('Name');
		$column->setSortable(true);
		$column->setSorted(Sort::ASC);

		Assert::with($this->source, function() use ($column) {
			$this->sort(['name' => $column]);
			Assert::same('u.name ASC', (string) $this->queryBuilder->getDQLPart('orderBy')[0]);
		});
	}


	public function testFilter(): void
	{
		$column = new TextColumn('Name');
		$column->setParent(null, 'name');

		$filter = new TextFilter('Name');
		$filter->setParent(null, 'name');
		$filter->setColumns($column);
		$filter->setValue('John');

		Assert::with($this->source, function() use ($filter) {
			$this->filter(['name' => $filter]);
			$where = (string) $this->queryBuilder->getDQLPart('where');
			Assert::contains('LOWER(u.name) LIKE LOWER(', $where);
		});
	}


	public function testLeavesJoinsUntouchedByDefault(): void
	{
		$this->assertJoinPruning(false);
	}


	public function testPrunesJoinsWhenEnabled(): void
	{
		$this->assertJoinPruning(true);
	}


	private function assertJoinPruning(bool $enabled): void
	{
		$conn = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
		$conn->executeStatement('CREATE TABLE pruning_users (id INTEGER PRIMARY KEY, group_id INTEGER)');
		$conn->insert('pruning_users', ['id' => 1, 'group_id' => 1]);

		$config = new Configuration;
		$config->setMetadataDriverImpl(new AttributeDriver([]));
		$config->enableNativeLazyObjects(true);
		$em = new EntityManager($conn, $config);
		$queries = new ArrayObject;
		$qb = new RecordingQueryBuilder($em, $queries);
		$qb->select('u')->from(PruningUser::class, 'u')
			->leftJoin(PruningUser::class, 'g', Join::WITH, 'g.id = u.groupId')
			->leftJoin(PruningUser::class, 'r', Join::WITH, 'r.id = u.id')
			->andWhere('g.id = 1');

		$source = new DoctrineORMSource($qb);
		$source->setLeftJoinsPruning($enabled);

		Assert::same($enabled, $source->isLeftJoinsPruning());
		Assert::same(1, $source->getCount());
		Assert::count(1, $source->fetchItems([], [], 0, 10));
		Assert::count(2, $queries);

		foreach ($queries as $dql) {
			Assert::contains(' g WITH ', $dql);
			if ($enabled) {
				Assert::notContains(' r WITH ', $dql);
			} else {
				Assert::contains(' r WITH ', $dql);
			}
		}

		Assert::contains(' g WITH ', $qb->getDQL());
		Assert::contains(' r WITH ', $qb->getDQL());
	}
}

(new DoctrineORMSourceTest)->run();
