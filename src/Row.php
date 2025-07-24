<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

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
	 * @param Item $item
	 */
	public function __construct(
		private object|array $item,
		private string $primaryKey,	// @phpstan-ignore property.onlyWritten
	) {
		$this->reader = PropertyAccess::createPropertyAccessor();

		$id = $this->getValue($primaryKey);

		if (!is_string($id) && !is_int($id)) {
			// todo: throw IdentifierInvalidException
			throw new \Exception;
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


	public function getValue(Column|string $column): mixed
	{
		if ($column instanceof Column) {
			$column = $column->getName();
		}

		if (!$column) {
			// todo: throw ColumnWithoutNameException
			throw new \Exception;
		}

		$path = match (true) {
			is_array($this->item) => '['.$column.']',

			default => $column,
		};

		return $this->reader->getValue($this->item, $path);
	}
}
