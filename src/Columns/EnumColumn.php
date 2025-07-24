<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use BackedEnum;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Row;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Html;

class EnumColumn extends AbstractColumn
{
	protected Align $align = Align::Right;


	public function renderValue(Row $row): void
	{
		$enum = $row->getValue($this);

		if (!$enum instanceof BackedEnum) {
			// todo: throw ColumnValueTypeException
			throw new \Exception;
		}

		echo match (true) {
			$enum instanceof LabeledEnum => Html::badgeEnum($enum),
			default => $enum->value,
		};
	}
}
