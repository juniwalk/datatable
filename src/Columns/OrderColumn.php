<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Columns\Interfaces\Exclusive;
use JuniWalk\DataTable\Columns\Interfaces\Hideable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Columns\Traits\Hiding;
use JuniWalk\DataTable\Columns\Traits\Sorting;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;
use JuniWalk\Utils\Format;
use Nette\Utils\Html;

class OrderColumn extends AbstractColumn implements Exclusive, Sortable, Hideable
{
	use Sorting, Hiding;

	protected Align $align = Align::Left;
	protected bool $isDisabled = false;


	public function setDisabled(bool $disabled = true): static
	{
		$this->isDisabled = $disabled;
		return $this;
	}


	public function isDisabled(): bool
	{
		return $this->isDisabled;
	}


	/**
	 * @throws FieldInvalidException
	 */
	protected function formatValue(Row $row): Html
	{
		$value = Format::numeric($row->getValue($this), strict: false);

		if (!is_numeric($value)) {
			throw FieldInvalidException::fromColumn($this, $value, 'numeric');
		}

		$value = number_format((float) $value, 0, '.', ' ');

		$html = Html::el('button class="btn btn-secondary btn-xs" style="cursor: move"')
			->addHtml(Html::el('i class="fa-solid fa-arrows-up-down fa-fw"'))
			->addText(' ')->addHtml(Html::el('strong', $value));

		return match ($this->isDisabled) {
			true	=> $html->addClass('disabled'),
			default	=> $html->data('dt-sort', true),
		};
	}
}

