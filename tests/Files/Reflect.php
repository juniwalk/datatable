<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Closure;
use ReflectionClass;
use ReflectionMethod;

class Reflect
{
	private function __construct() {}


	/**
	 * @template T of object
	 * @param  T|class-string<T> $className
	 * @return ReflectionClass<T>
	 */
	public static function class(object|string $className): ReflectionClass
	{
		if (is_object($className)) {
			$className = $className::class;
		}

		return new ReflectionClass($className);
	}


	/**
	 * @param  class-string $className
	 */
	public static function method(object|string $className, string $methodName): ReflectionMethod
	{
		return static::class($className)->getMethod($methodName);
	}


	public static function closure(object $object, string $methodName): Closure
	{
		$method = static::method($object, $methodName);

		if ($method->isStatic()) {
			$object = null;
		}

		return $method->getClosure($object);
	}
}
