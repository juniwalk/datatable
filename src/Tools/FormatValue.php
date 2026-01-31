<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Tools;

use BackedEnum;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Format;
use OutOfBoundsException;
use ValueError;

class FormatValue
{
	private function __construct() {}


	public static function string(mixed $value): ?string
	{
		$value = Format::stringify($value);

		if ($value === '') {
			$value = null;
		}

		return $value;
	}


	public static function number(mixed $value, ?int $precision = null): int|float|null
	{
		return Format::numeric($value, $precision);
	}


	/**
	 * @param  array<int|string, mixed> $values
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public static function index(mixed $value, array $values): int|string|null
	{
		if (!is_null($value) && !(is_int($value) || is_string($value))) {
			throw new InvalidArgumentException('Value of type "'.gettype($value).'" cannot be array index.');
		}

		if (!is_null($value) && !isset($values[$value])) {
			throw new OutOfBoundsException('Index "'.$value.'" is not in values list.');
		}

		return $value;
	}


	/**
	 * @template T of BackedEnum
	 * @param  class-string<T> $className
	 * @return ?T
	 * @throws ValueError
	 */
	public static function enum(mixed $value, string $className): ?BackedEnum
	{
		if (empty($value)) {
			return null;
		}

		if (is_object($value) && is_a($value, $className)) {
			return $value;
		}

		if (is_string($value) || is_int($value)) {
			if (is_a($className, LabeledEnum::class, true)) {
				return $className::make($value);
			}

			return $className::from($value);
		}

		throw new ValueError;
	}


	/**
	 * @param  string[] $formats
	 * @throws DateMalformedStringException
	 */
	public static function datetime(mixed $value, array $formats = []): ?DateTimeImmutable
	{
		if (empty($value)) {
			return null;
		}

		if ($value instanceof DateTimeInterface) {
			return DateTimeImmutable::createFromInterface($value);
		}

		if (is_numeric($value)) {
			return new DateTimeImmutable('@'.$value);
		}

		if (is_string($value)) {
			$formats = array_merge($formats, [
				DateTimeInterface::RFC3339_EXTENDED,
				DateTimeInterface::RFC3339,
				'Y-m-d H:i:s',
				'Y-m-d',
			]);

			foreach ($formats as $format) {
				$date = DateTimeImmutable::createFromFormat($format, $value);

				if ($date !== false) {
					return $date;
				}
			}

			return new DateTimeImmutable($value);
		}

		throw new DateMalformedStringException;
	}
}
