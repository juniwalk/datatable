<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\CustomRenderer;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Table;
use JuniWalk\DataTable\Traits;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Stringable;

abstract class AbstractColumn extends Control implements Column
{
	use Traits\Attributes;

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


	/**
	 * @throws FieldInvalidException
	 */
	public function render(Row $row): void
	{
		$value = $this->renderValue($row);

		if ($this instanceof CustomRenderer && $this->hasRenderer()) {
			$value = $this->renderCustom($row, $value);
		}

		if (!is_null($value) && !(is_string($value) || $value instanceof Stringable)) {
			throw FieldInvalidException::fromColumn($this, $value, 'string');
		}

		echo $value;
	}


	public function renderLabel(): void
	{
		echo $this->label;
	}


	abstract protected function renderValue(Row $row): Html|string;


	/**
	 * @throws InvalidStateException
	 */
	protected function validateParent(IContainer $container): void
	{
		$table = $container->getParent();

		if (!$table instanceof Table) {
			throw InvalidStateException::parentRequired(Table::class, $this);
		}

		$table->when('render', function() {
			$this->addAttribute('class', 'col-'.Strings::webalize($this->name));
			$this->addAttribute('class', $this->align->class());
		});

		parent::validateParent($container);
	}
}
