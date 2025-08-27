<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Interfaces\CallbackRenderable;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Table;
use JuniWalk\DataTable\Traits;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;
use Nette\Utils\Strings;

abstract class AbstractColumn extends Control implements Column
{
	use Traits\Attributes;
	use Traits\Translation;

	protected Align $align = Align::Left;

	protected ?string $field = null;


	public function __construct(
		protected string $label,
	) {
	}


	public function getLabel(): string
	{
		return $this->label;
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


	// ? Overriden using {Sorting, Filters, Hiding} traits
	public function isSortable(): ?bool { return false; }
	public function isFiltered(): bool { return false; }
	public function isHidden(): bool { return false; }


	/**
	 * @throws FieldInvalidException
	 */
	public function render(Row $row): void
	{
		$value = $this->formatValue($row);

		if ($this instanceof CallbackRenderable && $this->hasRenderer()) {
			$value = $this->callbackRender($row, $value);
		}

		echo $value;
	}


	public function renderLabel(): void
	{
		echo $this->translate($this->label);
	}


	abstract protected function formatValue(Row $row): Html|string;


	/**
	 * @throws InvalidStateException
	 */
	protected function validateParent(IContainer $parent): void
	{
		parent::validateParent($parent);

		$this->monitor($this::class, fn() => $this->lookup(Table::class));
		$this->monitor(Table::class, function(Table $table) {
			$this->setTranslator($table->getTranslator());

			$table->when('render', function() {
				$this->addAttribute('class', 'col-'.Strings::webalize($this->name));
				$this->addAttribute('class', $this->align->class());
			});
		});
	}
}
