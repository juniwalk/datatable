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

/**
 * @inheritDoc
 */
class LinkAction extends AbstractAction
{
	use LinkArguments;
	use LinkHandler;

	protected string $tag = 'a';


	public function render(?Row $row = null, bool $return = false): ?Html
	{
		$link = $this->createLink($this->dest ?? $this->name.'!', $this->createArgs($row));

		$button = parent::render($row, true);
		$button->setHref($link);

		if ($return === true) {
			return $button;
		}

		echo $button; return null;
	}
}
