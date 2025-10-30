<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Traits\LinkArguments;
use JuniWalk\DataTable\Traits\LinkHandler;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

class LinkColumn extends TextColumn
{
	use LinkArguments;
	use LinkHandler;

	/**
	 * @throws FieldNotFoundException
	 * @throws FieldInvalidException
	 * @throws InvalidLinkException
	 */
	protected function formatValue(Row $row): Html|string
	{
		if ($row->getValue($this) === null) {
			return '';
		}

		return Html::el('a')->setHtml(parent::formatValue($row))->addClass('fw-bold')
			->setHref($this->createLink($this->dest, $this->createArgs($row)));
	}
}
