<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters\Interfaces;

interface FilterRange
{
	/**
	 * @param null|array{from: mixed, to: mixed} $value
	 */
	public function setValue(?array $value): static;

	/**
	 * @return null|array{from: mixed, to: mixed}
	 */
	public function getValue(): ?array;

	/**
	 * @return null|array{from: scalar, to: scalar}
	 */
	public function getValueFormatted(): ?array;

	public function getValueFrom(): mixed;
	public function getValueTo(): mixed;
}
