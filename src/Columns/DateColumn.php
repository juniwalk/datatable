<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use DateTimeInterface;

class DateColumn extends AbstractColumn
{
	protected string $align = 'end';
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


	public function render(mixed $row): void
	{
		if (!$row instanceof DateTimeInterface) {
			throw new \Exception;
		}

		echo $row->format($this->format);
	}
}
