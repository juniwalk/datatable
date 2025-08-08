<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\Utils\Format;
use Nette\MemberAccessException;
use Nette\Utils\ObjectHelpers;

/**
 * @method static setClass(?string ...$value)
 * @method static addClass(?string ...$value)
 * @method ?string getClass()
 * @method bool hasClass(string ...$value)
 * @method static removeClass(string ...$value)
 * 
 * @method static setTitle(?string $value)
 * @method ?string getTitle()
 * @method bool hasTitle()
 */
trait Attributes
{
	/** @var array<string, string> */
	protected array $attributes = [];


	/**
	 * @param  string[] $args
	 * @throws MemberAccessException
	 */
	public function __call(string $name, array $args): mixed
	{
		$attr = explode('-', Format::kebabCase($name), 2);

		[$type, $attr] = $attr + ['', ''];

		return match ($type) {
			'remove' => $this->removeAttribute($attr, ...$args),
			'set' => $this->setAttribute($attr, ...$args),
			'add' => $this->addAttribute($attr, ...$args),
			'has' => $this->hasAttribute($attr, ...$args),
			'get' => $this->getAttribute($attr),

			default => ObjectHelpers::strictCall(static::class, $name),
		};
	}


	public function setAttribute(string $name, ?string ...$value): static
	{
		$this->attributes[$name] = implode(' ', $value);
		return $this;
	}


	public function addAttribute(string $name, ?string ...$value): static
	{
		if ($values = $this->attributes[$name] ?? null) {
			$value = [$values, ...$value];
		}

		return $this->setAttribute($name, ...$value);
	}


	/**
	 * @param array<string, string|string[]> $attributes
	 */
	public function setAttributes(array $attributes): static
	{
		foreach ($attributes as $name => $value) {
			$this->setAttribute($name, ... (array) $value);
		}

		return $this;
	}


	/**
	 * @param array<string, string|string[]> $attributes
	 */
	public function addAttributes(array $attributes): static
	{
		foreach ($attributes as $name => $value) {
			$this->addAttribute($name, ... (array) $value);
		}

		return $this;
	}


	public function getAttribute(string $name): ?string
	{
		return $this->attributes[$name] ?? null;
	}


	public function hasAttribute(string $name, string ...$value): bool
	{
		$exists = array_key_exists($name, $this->attributes);

		if (!$exists || empty($value)) {
			return $exists;
		}

		return (bool) array_intersect($this->fetchAttributeValues($name), $value);
	}


	/**
	 * @return array<string, string>
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}


	public function removeAttribute(string $name, string ...$value): static
	{
		if (empty($value) || !$this->hasAttribute($name)) {
			unset($this->attributes[$name]);
			return $this;
		}

		$values = $this->fetchAttributeValues($name);
		$values = array_diff($values, $value);

		$this->setAttribute($name, ...$values);

		if (empty($this->attributes[$name])) {
			unset($this->attributes[$name]);
		}

		return $this;
	}


	/**
	 * @return string[]
	 */
	private function fetchAttributeValues(string $name): array
	{
		if (!$values = $this->attributes[$name] ?? null) {
			return [];
		}

		return explode(' ', $values);
	}
}
