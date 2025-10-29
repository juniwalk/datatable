<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

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
use JuniWalk\DataTable\Traits\RendererCallback;
use JuniWalk\Utils\Format;

class NumberColumn extends AbstractColumn implements Sortable, Filterable, Hideable, CallbackRenderable
{
	use Sorting, Filters, Hiding, RendererCallback;

	protected Align $align = Align::Right;

	protected int $decimals = 0;
	protected ?string $decimalSeparator = '.';
	protected ?string $thousandsSeparator = ' ';


	public function setFormat(
		int $decimals = 0,
		?string $decimalSeparator = '.',
		?string $thousandsSeparator = ' ',
	): static {
		$this->decimals = $decimals;
		$this->decimalSeparator = $decimalSeparator;
		$this->thousandsSeparator = $thousandsSeparator;
		return $this;
	}


	public function getDecimals(): int
	{
		return $this->decimals;
	}


	public function getDecimalSeparator(): ?string
	{
		return $this->decimalSeparator;
	}


	public function getThousandsSeparator(): ?string
	{
		return $this->thousandsSeparator;
	}


	/**
	 * @throws FieldInvalidException
	 */
	protected function formatValue(Row $row): string
	{
		if (!$value = $row->getValue($this)) {
			return '';
		}

		$value = Format::numeric($value, strict: false);

		if (!is_numeric($value)) {
			throw FieldInvalidException::fromColumn($this, $value, 'numeric');
		}

		return number_format((float) $value, $this->decimals, $this->decimalSeparator, $this->thousandsSeparator);
	}
}
