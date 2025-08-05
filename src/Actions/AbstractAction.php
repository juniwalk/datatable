<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use Closure;
use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Traits;
use Nette\Application\UI\Control;
use Nette\Utils\Html;

abstract class AbstractAction extends Control implements Action
{
	use Traits\Attributes;

	protected Closure $rowAllowed;


	public function __construct(
		protected string $label,
	) {
	}


	public function setLabel(string $label): self
	{
		$this->label = $label;
		return $this;
	}


	public function getLabel(): string
	{
		return $this->label;
	}


	public function setRowAllowed(Closure $condition): self
	{
		$this->rowAllowed = $condition;
		return $this;
	}


	public function isRowAllowed(Row $row): bool
	{
		if (!isset($this->rowAllowed)) {
			return true;
		}

		return (bool) call_user_func($this->rowAllowed, $row->getItem());
	}


	public function render(Row $row): Html
	{
		$button = Html::el('a', $this->attributes);

		// todo: handle translation
		// todo: handle icons

		return $button->addText($this->label);
	}
}
