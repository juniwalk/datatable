<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use DateMalformedStringException;
use DateTimeInterface;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Hideable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Columns\Traits\Filters;
use JuniWalk\DataTable\Columns\Traits\Hiding;
use JuniWalk\DataTable\Columns\Traits\Sorting;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Interfaces\CallbackRenderable;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Tools\FormatValue;
use JuniWalk\DataTable\Traits\RendererCallback;
use Nette\Utils\Html;

class DateColumn extends AbstractColumn implements Sortable, Filterable, Hideable, CallbackRenderable
{
	use Sorting, Filters, Hiding, RendererCallback;

	protected Align $align = Align::Right;

	protected string $format = 'j. n. Y';


	public function setFormat(string $format): static
	{
		$this->format = $format;
		return $this;
	}


	public function getFormat(): string
	{
		return $this->format;
	}


	/**
	 * @throws FieldInvalidException
	 */
	protected function formatValue(Row $row): Html|string
	{
		if (!$value = $row->getValue($this)) {
			return '';
		}

		try {
			$value = FormatValue::dateTime($value, [
				$this->format,
			]);

		} catch (DateMalformedStringException $e) {
			throw FieldInvalidException::fromColumn($this, $value, DateTimeInterface::class, $e);
		}

		return $value?->format($this->format) ?? '';
	}
}
