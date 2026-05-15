<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters\Interfaces;

use JuniWalk\DataTable\Filter;

interface FilterList extends Filter
{
	/**
	 * @param  mixed[] $value
	 * @return mixed[]
	 */
	public function checkValue(?array $value): ?array;

	/**
	 * @param mixed[] $value
	 */
	public function setValue(?array $value): static;

	/**
	 * @return mixed[]
	 */
	public function getValue(): ?array;

	/**
	 * @return scalar[]
	 */
	public function getValueFormatted(): ?array;
}
