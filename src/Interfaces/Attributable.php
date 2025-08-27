<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Interfaces;

interface Attributable
{
	public function setAttribute(string $name, ?string ...$value): static;

	public function addAttribute(string $name, ?string ...$value): static;

	/**
	 * @param array<string, string|string[]> $attributes
	 */
	public function setAttributes(array $attributes): static;

	/**
	 * @param array<string, string|string[]> $attributes
	 */
	public function addAttributes(array $attributes): static;

	public function getAttribute(string $name): ?string;

	public function hasAttribute(string $name, string ...$value): bool;

	/**
	 * @return array<string, string>
	 */
	public function getAttributes(): array;

	public function removeAttribute(string $name, string ...$value): static;
}
