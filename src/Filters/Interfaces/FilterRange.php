<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters\Interfaces;

interface FilterRange
{
	/**
	 * @param  array{from?: mixed, to?: mixed} $value
	 * @return array{from: mixed, to: mixed}
	 */
	public function checkValue(?array $value): array;

	/**
	 * @param array{from?: mixed, to?: mixed} $value
	 */
	public function setValue(?array $value): static;

	/**
	 * @return array{from: mixed, to: mixed}
	 */
	public function getValue(): ?array;

	/**
	 * @return array{from: scalar, to: scalar}
	 */
	public function getValueFormatted(): ?array;

	public function getValueFrom(): mixed;
	public function getValueTo(): mixed;
}
