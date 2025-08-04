<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Traits;
use Nette\Application\UI\Control;
use Nette\Utils\Html;

abstract class AbstractAction extends Control implements Action
{
	use Traits\Attributes;

	public function __construct(
		protected string $label,
	) {
	}


	public function getLabel(): string
	{
		return $this->label;
	}


	public function render(Row $row): Html
	{
		$button = Html::el('a', $this->attributes);

		// todo: handle translation
		// todo: handle icons

		return $button->addText($this->label);
	}
}
