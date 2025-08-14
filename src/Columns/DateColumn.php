<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use DateTimeInterface;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Columns\Traits\Filters;
use JuniWalk\DataTable\Columns\Traits\Sorting;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Interfaces\CustomRenderer;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Traits\Renderer;
use Nette\Utils\Html;

class DateColumn extends AbstractColumn implements Sortable, Filterable, CustomRenderer
{
	use Sorting, Filters, Renderer;

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
	protected function renderValue(Row $row): Html|string
	{
		if (!$value = $row->getValue($this)) {
			return '';
		}

		// todo: try to make DateTime from other value types

		if (!$value instanceof DateTimeInterface) {
			throw FieldInvalidException::fromColumn($this, $value, DateTimeInterface::class);
		}

		return $value->format($this->format);
	}
}
