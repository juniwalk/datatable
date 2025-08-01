<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @phpstan-import-type Item from Source
 */
class Row
{
	private int|string $id;
	private PropertyAccessor $reader;

	/**
	 * @param  Item $item
	 * @throws FieldNotFoundException
	 * @throws FieldInvalidException
	 */
	public function __construct(
		private object|array $item,
		private readonly Source $source,
	) {
		$this->reader = PropertyAccess::createPropertyAccessor();
		$primaryKey = $source->getPrimaryKey();

		$id = $this->getValue($primaryKey);

		if (!is_string($id) && !is_int($id)) {
			throw FieldInvalidException::fromName($primaryKey, $id, 'int|string');
		}

		$this->id = $id;
	}


	public function getId(): int|string
	{
		return $this->id;
	}


	/**
	 * @return Item
	 */
	public function getItem(): object|array
	{
		return $this->item;
	}


	public function getPrimaryKey(): string
	{
		return $this->source->getPrimaryKey();
	}


	/**
	 * @throws FieldNotFoundException
	 */
	public function getValue(Column|string $column): mixed
	{
		if ($column instanceof Column) {
			$column = $column->getName();
		}

		if (!$column) {
			throw FieldNotFoundException::fromName($column ?? '');
		}

		$path = match (true) {
			is_array($this->item) => '['.$column.']',

			default => $column,
		};

		return $this->reader->getValue($this->item, $path);
	}
}
