<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Traits\Linking;
use Nette\Utils\Html;

/**
 * @inheritDoc
 */
class LinkAction extends AbstractAction
{
	use Linking;

	public function render(Row $row): Html
	{
		$link = $this->createLink($this->dest ?? $this->name.'!', $this->createArgs($row));

		$button = parent::render($row);
		$button->setHref($link);

		return $button;
	}
}
