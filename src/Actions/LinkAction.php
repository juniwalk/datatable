<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Traits\LinkArguments;
use JuniWalk\DataTable\Traits\LinkHandler;
use Nette\Utils\Html;

class LinkAction extends AbstractAction
{
	use LinkArguments;
	use LinkHandler;

	protected string $tag = 'a';


	public function createButton(?Row $row): Html
	{
		$link = $this->createLink($this->dest ?? $this->getName().'!', $this->createArgs($row));

		$button = parent::createButton($row);
		$button->setHref($link);

		return $button;
	}
}
