<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files\Enums;

use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum WordsOnly: string implements LabeledEnum
{
	use Labeled;

	case Yes = 'yes';
	case No = 'no';


	public function label(): string
	{
		return match ($this) {
			self::Yes => 'Yes',
			self::No => 'No',
		};
	}
}
