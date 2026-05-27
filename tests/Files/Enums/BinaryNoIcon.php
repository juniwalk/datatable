<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files\Enums;

use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum BinaryNoIcon: string implements LabeledEnum
{
	use Labeled;

	case Yes = '1';
	case No = '0';


	public function label(): string
	{
		return match ($this) {
			self::Yes => 'Yes',
			self::No => 'No',
		};
	}
}
