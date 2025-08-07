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

	protected Closure|bool $allowCondition;
	protected string $tag = 'span';


	public function __construct(
		protected string $label,
		protected ?string $group = null,
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


	public function setGroup(?string $group): self
	{
		$this->group = $group;
		return $this;
	}


	public function getGroup(): ?string
	{
		return $this->group;
	}


	public function setAllowCondition(Closure|bool $condition): self
	{
		$this->allowCondition = $condition;
		return $this;
	}


	public function isAllowed(?Row $row = null): bool
	{
		if (!isset($this->allowCondition)) {
			return true;
		}

		if (is_bool($this->allowCondition)) {
			return $this->allowCondition;
		}

		return (bool) call_user_func($this->allowCondition, $row?->getItem());
	}


	public function render(?Row $row = null, bool $return = false): ?Html
	{
		// todo: make sure there is proper button size? btn-xs
		// ? should be only optional, so if size is provided, do not add standard one

		$button = Html::el($this->tag, $this->attributes);

		// todo: handle translation
		// todo: handle icons

		$button->addText($this->label);

		if ($return === true) {
			return $button;
		}

		echo $button; return null;
	}
}
