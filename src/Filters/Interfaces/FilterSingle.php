<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters\Interfaces;

interface FilterSingle
{
	/**
	 * @param null|mixed $value
	 */
	public function setValue(mixed $value): static;

	/**
	 * @return null|mixed
	 */
	public function getValue(): mixed;

	/**
	 * @return null|scalar
	 */
	public function getValueFormatted(): mixed;
}
