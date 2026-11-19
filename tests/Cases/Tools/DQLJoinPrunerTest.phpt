<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Tools;

require __DIR__ . '/../../bootstrap.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use JuniWalk\DataTable\Tools\DQLJoinPruner;
use ReflectionClass;
use Tester\Assert;
use Tester\TestCase;

class DQLJoinPrunerTest extends TestCase
{
	private QueryBuilder $qb;


	public function setUp(): void
	{
		$em = (new ReflectionClass(EntityManager::class))->newInstanceWithoutConstructor();
		$this->qb = (new QueryBuilder($em))->select('u')->from('User', 'u');
	}


	public function testPrunesUnusedLeftJoins(): void
	{
		$this->qb
			->leftJoin('u.groups', 'g')
			->leftJoin('u.roles', 'r')
			->orderBy('u.name');

		DQLJoinPruner::prune($this->qb);

		Assert::same([], $this->qb->getDQLPart('join'));
	}


	public function testKeepsSortedJoinAndItsDependencies(): void
	{
		$this->qb
			->leftJoin('u.groups', 'g')
			->leftJoin('g.members', 'm')
			->leftJoin('u.roles', 'r')
			->orderBy('m.name');

		DQLJoinPruner::prune($this->qb);

		$joins = $this->qb->getDQLPart('join')['u'];
		Assert::same(['g', 'm'], array_map(static fn(Join $join) => $join->getAlias(), $joins));
	}


	public function testKeepsJoinsUsedByFilters(): void
	{
		$this->qb
			->leftJoin('u.groups', 'g')
			->leftJoin('u.roles', 'r')
			->andWhere('g.id = :group')
			->setParameter('group', 1);

		DQLJoinPruner::prune($this->qb);

		$joins = $this->qb->getDQLPart('join')['u'];
		Assert::same(['g'], array_map(static fn(Join $join) => $join->getAlias(), $joins));
	}


	public function testPreservesRequiredJoinKindsAndSelections(): void
	{
		$this->qb
			->leftJoin('u.groups', 'g')
			->innerJoin('u.members', 'm')
			->leftJoin('u.roles', 'r')
			->addSelect('r')
			->leftJoin('u.notes', 'n', Join::WITH, 'n.active = :active')
			->setParameter('active', true);

		DQLJoinPruner::prune($this->qb);

		$joins = $this->qb->getDQLPart('join')['u'];
		Assert::same(['m', 'r', 'n'], array_map(static fn(Join $join) => $join->getAlias(), $joins));
	}
}

(new DQLJoinPrunerTest)->run();
