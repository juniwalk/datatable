<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pruning_users')]
class PruningUser
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer')]
	public int $id;

	#[ORM\Column(name: 'group_id', type: 'integer', nullable: true)]
	public ?int $groupId;
}
