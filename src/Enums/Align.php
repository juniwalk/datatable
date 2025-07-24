<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Enums;

use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum Align: string implements LabeledEnum
{
	use Labeled;

	case Left = 'left';
	case Center = 'center';
	case Right = 'right';


	public function label(): string
	{
		return $this->value;
	}


	public function icon(): string
	{
		return match ($this) {
			self::Left => 'fa-align-left',
			self::Center => 'fa-align-center',
			self::Right => 'fa-align-right',
		};
	}


	public function class(): string
	{
		return 'text-'.match ($this) {
			self::Left => 'start',
			self::Right => 'end',
			default => $this->value,
		};
	}
}
