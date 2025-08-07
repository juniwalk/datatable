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
	protected function renderValue(Row $row): Html|string
	{
		$link = $this->createLink($this->dest, $this->createArgs($row));
		$value = (string) parent::renderValue($row);

		return Html::el('a', $value)->setHref($link)
			->addClass('fw-bold');
	}
}
