<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Hideable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Columns\Traits\Filters;
use JuniWalk\DataTable\Columns\Traits\Hiding;
use JuniWalk\DataTable\Columns\Traits\Sorting;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Enums\Status;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Interfaces\CallbackRenderable;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Tools\FormatValue;
use JuniWalk\DataTable\Traits\RendererCallback;
use JuniWalk\Utils\Enums\Active;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Html as CustomHtml;
use Nette\Utils\Html;
use ValueError;

use function is_string;

class StatusColumn extends AbstractColumn implements Sortable, Filterable, Hideable, CallbackRenderable
{
	use Sorting, Filters, Hiding, RendererCallback;

	protected Align $align = Align::Right;
	protected Status $format = Status::Badge;

	/** @var class-string<LabeledEnum> */
	protected string $enum = Active::class;


	/**
	 * @param  value-of<Status> $format
	 * @throws ValueError
	 */
	public function setFormat(Status|string $format): static
	{
		if (is_string($format)) {
			$format = Status::from($format);
		}

		$this->format = $format;
		return $this;
	}


	public function getFormat(): Status
	{
		return $this->format;
	}


	/**
	 * @param class-string<LabeledEnum> $enum
	 */
	public function setEnum(string $enum): static
	{
		$this->enum = $enum;
		return $this;
	}


	/**
	 * @return class-string<LabeledEnum>
	 */
	public function getEnum(): string
	{
		return $this->enum;
	}


	protected function formatValue(Row $row): Html
	{
		$value = FormatValue::boolean($row->getValue($this));

		try {
			$status = $this->enum::make($value ?? '');

		} catch (ValueError $e) {
			throw FieldInvalidException::fromColumn($this, $value, 'bool', $e);
		}

		$label = $status->label();
		$color = $status->color();
		$icon = $status->icon();

		if ($this->format === Status::Icon && !$icon) {
			$this->format = Status::Badge;
		}

		return match ($this->format) {
			Status::Badge => CustomHtml::badgeEnum($status),
			Status::Label => CustomHtml::highlight($label, $color),
			Status::Icon  => CustomHtml::icon($icon, color: $color)
				->setAttribute('title', $this->translate($label))
				->setAttribute('data-bs-toggle', 'tooltip'),
		};
	}
}
