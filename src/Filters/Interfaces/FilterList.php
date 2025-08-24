<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters\Interfaces;

interface FilterList
{
	/**
	 * @param null|mixed[] $value
	 */
	public function setValue(?array $value): static;

	/**
	 * @return null|mixed[]
	 */
	public function getValue(): ?array;

	/**
	 * @return null|scalar[]
	 */
	public function getValueFormatted(): ?array;
}
