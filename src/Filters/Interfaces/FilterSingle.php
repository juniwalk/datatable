<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters\Interfaces;

interface FilterSingle
{
	public function checkValue(mixed $value): mixed;
	public function setValue(mixed $value): static;
	public function getValue(): mixed;

	/**
	 * @return null|scalar
	 */
	public function getValueFormatted(): mixed;
}
