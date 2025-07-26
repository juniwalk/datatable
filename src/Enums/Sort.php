<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Enums;

use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Enums\Traits\Labeled;

enum Sort: string implements LabeledEnum
{
	use Labeled;

	case ASC = 'asc';
	case DESC = 'desc';


	public function label(): string
	{
		return $this->value;
	}


	public function icon(): string
	{
		return match ($this) {
			self::ASC => 'fa-sort-up',
			self::DESC => 'fa-sort-down',
		};
	}


	public function order(): int
	{
		return match ($this) {
			self::ASC => SORT_ASC,
			self::DESC => SORT_DESC,
		};
	}
}
