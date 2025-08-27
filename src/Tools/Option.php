<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Tools;

use BackedEnum;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Enums\Color;

readonly class Option
{
	final public function __construct(
		public int|string $value,
		public string $label,
		public ?string $icon = null,
		public ?Color $color = null,
	) {
	}


	public static function fromEnum(BackedEnum $enum): static
	{
		if (!$enum instanceof LabeledEnum) {
			return new static($enum->value, $enum->name);
		}

		return new static($enum->value, $enum->label(), $enum->icon(), $enum->color());
	}
}
