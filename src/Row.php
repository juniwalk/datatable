<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Traits\Attributes;
use Stringable;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Throwable;

/**
 * @phpstan-import-type Item from Source
 */
class Row
{
	use Attributes;

	protected PropertyAccessor $reader;
	protected int|string $id;


	/**
	 * @param  Item $item
	 * @throws FieldNotFoundException
	 * @throws FieldInvalidException
	 */
	public function __construct(
		protected object|array $item,
		protected readonly string $primaryKey,
	) {
		$this->reader = PropertyAccess::createPropertyAccessor();

		$this->fetchPrimaryKey($primaryKey);
		$this->setAttribute('class', 'align-middle');
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
		return $this->primaryKey;
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

		try {
			return $this->reader->getValue($this->item, $path);

		} catch (Throwable) {
			return null;
		}
	}


	/**
	 * @throws FieldNotFoundException
	 * @throws FieldInvalidException
	 */
	protected function fetchPrimaryKey(string $primaryKey): void
	{
		$id = $this->getValue($primaryKey);

		if (is_null($id)) {
			throw FieldNotFoundException::fromName($primaryKey);
		}

		if ($id instanceof Stringable) {
			$id = (string) $id;
		}

		if (!is_int($id) && (!is_string($id) || $id === '')) {
			throw FieldInvalidException::fromName($primaryKey, $id, 'int|non-empty-string');
		}

		$this->id = $id;
	}
}
