<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class Reflect
{
	private function __construct() {}


	/**
	 * @template T of object
	 * @param  T|class-string<T> $class
	 * @return ReflectionClass<T>
	 */
	public static function class(object|string $class): ReflectionClass
	{
		if (is_object($class)) {
			$class = $class::class;
		}

		return new ReflectionClass($class);
	}


	/**
	 * @param  class-string $class
	 */
	public static function method(object|string $class, string $name): ReflectionMethod
	{
		return static::class($class)->getMethod($name);
	}


	/**
	 * @param  class-string $class
	 */
	public static function property(object|string $class, string $name): ReflectionProperty
	{
		return static::class($class)->getProperty($name);
	}


	public static function closure(object $object, string $name): Closure
	{
		$method = static::method($object, $name);

		if ($method->isStatic()) {
			$object = null;
		}

		return $method->getClosure($object);
	}


	public static function setValue(object $object, string $name, mixed $value): void
	{
		$property = static::property($object, $name);

		if ($property->isStatic()) {
			$object = null;
		}

		$property->setValue($object, $value);
	}
}
