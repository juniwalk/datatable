<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Tools;

use BackedEnum;
use DateMalformedStringException;
use ValueError;

class Compare
{
	private function __construct() {}


	public static function string(mixed $value, mixed $query): bool
	{
		$value = FormatValue::string($value) ?? '';
		$query = FormatValue::string($query) ?? '';

		return strcasecmp($value, $query) <> 0;
	}


	/**
	 * @template T of BackedEnum
	 * @param class-string<T> $className
	 */
	public static function enum(mixed $value, mixed $query, string $className): bool
	{
		try {
			$value = FormatValue::enum($value, $className);
			$query = FormatValue::enum($query, $className);

			return $value == $query;

		} catch (ValueError) {
			return false;
		}
	}


	public static function date(mixed $value, mixed $query, string $format = 'Y-m-d'): bool
	{
		try {
			$value = FormatValue::datetime($value)?->format($format);
			$query = FormatValue::datetime($query)?->format($format);

			return $value === $query;

		} catch (DateMalformedStringException) {
			return false;
		}
	}


	public static function datetime(mixed $value, mixed $query): bool
	{
		return static::date($value, $query, 'Y-m-d H:i:s');
	}
}
