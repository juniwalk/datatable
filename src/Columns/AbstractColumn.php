<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\CustomRenderer;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Row;
use Nette\Application\UI\Control;
use Nette\Utils\Helpers;
use Throwable;

abstract class AbstractColumn extends Control implements Column
{
	protected Align $align = Align::Left;

	protected ?string $field = null;


	public function __construct(
		protected string $label,
	) {
	}


	public function setField(?string $field): self
	{
		$this->field = $field;
		return $this;
	}


	public function getField(): ?string
	{
		return $this->field;
	}


	/**
	 * @param value-of<Align> $align
	 */
	public function setAlign(Align|string $align): self
	{
		$this->align = Align::make($align);
		return $this;
	}


	public function getAlign(): Align
	{
		return $this->align;
	}


	// ? Overriden using {Filters|Sorting} traits
	public function isSortable(): ?bool
	{
		return false;
	}


	// ? Overriden using {Filters|Sorting} traits
	public function isFiltered(): bool
	{
		return false;
	}


	public function render(Row $row): void
	{
		try {
			if (!$this instanceof CustomRenderer) {
				// todo: throw ColumnRendererException
				throw new \Exception;
			}

			$value = $this->renderCustom($row);

		} catch (Throwable) {
			$value = Helpers::capture(fn() => $this->renderValue($row));
		}

		if ($value !== null && !is_scalar($value)) {
			// todo: throw ColumnValueTypeException
			throw new \Exception;
		}

		echo $value;
	}


	public function renderLabel(): void
	{
		echo $this->label;
	}
}
