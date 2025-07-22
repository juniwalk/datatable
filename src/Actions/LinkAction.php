<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Traits\Linking;
use Nette\Utils\Html;

class LinkAction extends AbstractAction
{
	use Linking;

	public function render(Row $row): Html
	{
		$button = Html::el('a class="btn btn-xs btn-secondary"', $this->label);
		$button->setHref($this->createLink($this->getName().'!', [
			'id' => $row->getId(),
		]));

		return $button;
	}
}
