<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Columns\Interfaces\CustomRenderer;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Row;

class NumberColumn extends AbstractColumn implements Sortable, Filterable, CustomRenderer
{
	use Traits\Sorting;
	use Traits\Filters;
	use Traits\Renderer;

	protected Align $align = Align::Right;

	protected int $precision = 0;

	protected ?string $separator = '.';


	public function setFormat(int $precision = 0, ?string $separator = '.'): self
	{
		$this->precision = $precision;
		$this->separator = $separator;
		return $this;
	}


	public function getPrecision(): int
	{
		return $this->precision;
	}


	public function getSeparator(): ?string
	{
		return $this->separator;
	}


	public function renderValue(Row $row): void
	{
		$number = $row->getValue($this);

		if (!is_numeric($number)) {
			// todo: throw ColumnValueTypeException
			throw new \Exception;
		}

		echo number_format((float) $number, $this->precision, $this->separator, ' ');
	}
}
