<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use DateTimeInterface;
use JuniWalk\DataTable\Columns\Interfaces\CustomRenderer;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;

class DateColumn extends AbstractColumn implements Sortable, Filterable, CustomRenderer
{
	use Traits\Sorting;
	use Traits\Filters;
	use Traits\Renderer;

	protected Align $align = Align::Right;

	protected string $format = 'j. n. Y';


	public function setFormat(string $format): self
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
	public function renderValue(Row $row): void
	{
		$date = $row->getValue($this);

		// todo: try to make DateTime from other value types

		if (!$date instanceof DateTimeInterface) {
			throw FieldInvalidException::fromColumn($this, $date, DateTimeInterface::class);
		}

		echo $date->format($this->format);
	}
}
