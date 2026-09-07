<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Sources;

require __DIR__ . '/../../bootstrap.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filters\TextFilter;
use JuniWalk\DataTable\SourceFactory;
use JuniWalk\DataTable\Sources\DoctrineORMSource;
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
}

(new DoctrineORMSourceTest)->run();
