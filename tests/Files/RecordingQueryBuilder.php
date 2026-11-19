<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use ArrayObject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class RecordingQueryBuilder extends QueryBuilder
{
	/** @param ArrayObject<int, string> $queries */
	public function __construct(EntityManagerInterface $em, private ArrayObject $queries)
	{
		parent::__construct($em);
	}


	public function getQuery(): Query
	{
		$this->queries[] = $this->getDQL();
		return parent::getQuery();
	}
}
