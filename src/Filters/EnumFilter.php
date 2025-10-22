<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use BackedEnum;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\DataTable\Tools\FormatValue;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Html;
use Nette\Forms\Form;
use Throwable;

/**
 * @template T of BackedEnum
 */
class EnumFilter extends AbstractFilter implements FilterSingle
{
	/** @var ?T */
	protected ?BackedEnum $value = null;

	protected string|bool $placeholder = true;


	/**
	 * @param class-string<T> $enum
	 */
	public function __construct(
		protected string $label,
		protected string $enum,
	) {
	}


	/**
	 * @return class-string<T>
	 */
	public function getEnumType(): string
	{
		return $this->enum;
	}


	public function setPlaceholder(string|bool $placeholder): static
	{
		$this->placeholder = $placeholder;
		return $this;
	}


	public function getPlaceholder(): string|bool
	{
		return $this->placeholder;
	}


	/**
	 * @throws FilterValueInvalidException
	 */
	public function setValue(mixed $value): static
	{
		try {
			$this->value = FormatValue::enum($value, $this->enum);
			$this->isFiltered = $this->value !== null;

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, $this->enum, $value, $e);
		}

		return $this;
	}


	/**
	 * @return ?T
	 */
	public function getValue(): mixed
	{
		return $this->value ?? null;
	}


	/**
	 * @return string|int|null
	 */
	public function getValueFormatted(): mixed
	{
		return $this->value?->value;
	}


	/**
	 * @return array<value-of<T>, Html>
	 */
	public function getItems(): array	// @phpstan-ignore return.unresolvableType
	{
		$items = [];

		foreach ($this->enum::cases() as $case) {
			$option = Html::option($case->name, $case->value);

			if ($case instanceof LabeledEnum) {
				$option = Html::optionEnum($case, true);
			}

			$items[$case->value] = $option;
		}

		return $items;
	}


	public function attachToForm(Form $form): void
	{
		$placeholder = $this->placeholder;

		if ($placeholder === true) {
			$placeholder = 'datatable.filter.select-placeholder';
		}

		$form->addSelect($this->fieldName(), $this->label, $this->getItems())
			->setValue($this->value ?? null)
			->setPrompt($placeholder);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()] ?? null);
		};
	}
}
