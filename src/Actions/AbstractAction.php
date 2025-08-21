<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use Closure;
use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Table;
use JuniWalk\DataTable\Traits;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;

abstract class AbstractAction extends Control implements Action
{
	use Traits\Attributes;
	use Traits\Confirmation;
	use Traits\Translation;
	use Traits\Icons;

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


	public function render(?Row $row = null): void
	{
		echo $this->createButton($row);
	}


	public function createButton(?Row $row): Html
	{
		$button = Html::el($this->tag, $this->attributes);

		if ($confirm = $this->createConfirm($row)) {
			$button->setAttribute('data-confirm', $confirm);
		}

		if ($icon = $this->createIcon()) {
			$button->addHtml($icon);
			$button->addText(' ');
		}

		$label = $this->translate($this->label);
		$button->addText($label);

		if ($title = $button->getTitle()) {
			$button->setTitle($this->translate($title));
		}

		return $button;
	}


	protected function validateParent(IContainer $parent): void
	{
		$this->monitor($this::class, fn() => $this->lookup(Table::class));
		$this->monitor(Table::class, function(Table $table) {
			$this->setTranslator($table->getTranslator());
		});

		parent::validateParent($parent);
	}
}
