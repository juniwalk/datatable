<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

class Row
{
	public function __construct(
		private int|string $id,
		private mixed $item,
	) {
	}


	public function getId(): int|string
	{
		return $this->id;
	}


	public function getValue(Column $column): mixed
	{
		// todo: use symfony/property-accessor to get the value
		return $this->item[$column->getName()] ?? null;
	}
}
