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
 * @method static setClass(mixed $value)
 * @method static addClass(mixed $value)
 * @method mixed getClass()
 * @method bool hasClass()
 * 
 * @method static setTitle(string $value)
 * @method string getTitle()
 * @method bool hasTitle()
 * 
 * @phpstan-type AttributeValue string|int|float|bool|null
 * @phpstan-type AttributeList array<string, AttributeValue|AttributeValue[]>
 */
trait Attributes
{
	/** @var AttributeList */
	protected array $attributes = [];


	/**
	 * @throws MemberAccessException
	 */
	public function __call(string $name, array $args): mixed	// @phpstan-ignore missingType.iterableValue (too complex to care)
	{
		$type = mb_substr($name, 0, 3);
		$attr = mb_substr($name, 3);

		$attr = Format::kebabCase($attr);

		return match ($type) {
			'set' => $this->setAttribute($attr, ...$args),
			'add' => $this->addAttribute($attr, ...$args),
			'has' => $this->hasAttribute($attr, ...$args),
			'get' => $this->getAttribute($attr),
			'del' => $this->delAttribute($attr),

			default => ObjectHelpers::strictCall(static::class, $name),
		};
	}


	/**
	 * @param AttributeValue|AttributeValue[] $value
	 */
	public function setAttribute(string $name, mixed $value): static
	{
		$this->attributes[$name] = $value;
		return $this;
	}


	/**
	 * @param AttributeValue|AttributeValue[] $value
	 */
	public function addAttribute(string $name, mixed $value): static
	{
		if ($values = $this->attributes[$name] ?? null) {
			$value = [... (array) $values, ... (array) $value];
		}

		return $this->setAttribute($name, $value);
	}


	/**
	 * @param AttributeList $attributes
	 */
	public function setAttributes(array $attributes): static
	{
		foreach ($attributes as $name => $value) {
			$this->setAttribute($name, $value);
		}

		return $this;
	}


	/**
	 * @param AttributeList $attributes
	 */
	public function addAttributes(array $attributes): static
	{
		foreach ($attributes as $name => $value) {
			$this->addAttribute($name, $value);
		}

		return $this;
	}


	/**
	 * @return AttributeValue
	 */
	public function getAttribute(string $name): mixed
	{
		if (!$values = $this->attributes[$name] ?? null) {
			return null;
		}

		if (is_array($values)) {
			$values = implode(' ', $values);
		}

		return $values;
	}


	/**
	 * @param AttributeValue|AttributeValue[] $value
	 */
	public function hasAttribute(string $name, mixed $value = null, bool $strict = false): bool
	{
		if (!isset($this->attributes[$name])) {
			return false;
		}

		if (is_null($value)) {
			return true;
		}

		$attr = $this->getAttribute($name);
		$attr = explode(' ', (string) $attr);

		$value = implode(' ', (array) $value);
		$value = explode(' ', $value);

		$found = array_intersect($attr, $value);

		if ($strict === false) {
			return (bool) $found;
		}

		return ! (bool) array_diff($value, $found);
	}


	/**
	 * @return AttributeList
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}


	public function delAttribute(string $name): static
	{
		unset($this->attributes[$name]);
		return $this;
	}
}
