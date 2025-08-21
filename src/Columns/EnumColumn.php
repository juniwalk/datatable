<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use BackedEnum;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Html as CustomHtml;
use Nette\Utils\Html;

class EnumColumn extends TextColumn
{
	protected Align $align = Align::Right;


	/**
	 * @throws FieldInvalidException
	 */
	protected function formatValue(Row $row): Html|string
	{
		if (!$value = $row->getValue($this)) {
			return '';
		}

		if (!$value instanceof BackedEnum) {
			throw FieldInvalidException::fromColumn($this, $value, BackedEnum::class);
		}

		return match (true) {
			$value instanceof LabeledEnum => CustomHtml::badgeEnum($value),
			default => (string) $value->value,
		};
	}
}
