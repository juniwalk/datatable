<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Traits\Linking;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;
use Nette\Utils\Html;

class LinkColumn extends TextColumn
{
	use Linking;


	/**
	 * @throws FieldInvalidException
	 */
	protected function renderValue(Row $row): Html|string
	{
		$value = (string) parent::renderValue($row);

		$link = Html::el('a', $value);
		$link->addClass('fw-bold');
		$link->setHref('/');

		return $link;
	}
}
