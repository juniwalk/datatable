<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use BackedEnum;
use JuniWalk\DataTable\Columns\Interfaces\CustomRenderer;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;
use Stringable;

class TextColumn extends AbstractColumn implements Sortable, Filterable, CustomRenderer
{
	use Traits\Sorting;
	use Traits\Filters;
	use Traits\Renderer;


	/**
	 * @throws FieldInvalidException
	 */
	public function renderValue(Row $row): void
	{
		$text = $row->getValue($this);

		if ($text instanceof Stringable) {
			$text = (string) $text;
		}

		if ($text instanceof BackedEnum) {
			$text = $text->value;
		}

		if (!is_scalar($text)) {
			throw FieldInvalidException::fromColumn($this, $text, 'string');
		}

		// convert to string
		echo $text;
	}
}
