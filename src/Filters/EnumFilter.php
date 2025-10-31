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
use OutOfBoundsException;
use Throwable;

/**
 * @template T of BackedEnum
 */
class EnumFilter extends AbstractFilter implements FilterSingle
{
	protected string|bool $placeholder = true;

	/** @var array<int|string, T> */
	protected array $items;

	/** @var ?T */
	protected ?BackedEnum $value;


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
		$items = $this->getItems();

		try {
			$value = FormatValue::enum($value, $this->enum);

			if ($value && !in_array($value, $items)) {
				throw new OutOfBoundsException('Value "'.$value->value.'" is not in items list.');
			}

			$this->value = $value ?: null;
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
	 * @param  T[] $items
	 * @throws FilterValueInvalidException
	 */
	public function setItems(array $items): static
	{
		$this->items = [];

		foreach ($items as $item) {
			if (!is_a($item, $this->enum)) {
				throw FilterValueInvalidException::fromFilter($this, $this->enum, $item);
			}

			$this->items[$item->value] = $item;
		}

		return $this;
	}


	/**
	 * @return T[]
	 */
	public function getItems(): array
	{
		return $this->items ?? $this->enum::cases();
	}


	public function attachToForm(Form $form): void
	{
		$items = static::convert($this->getItems());
		$placeholder = match ($this->placeholder) {
			true => 'datatable.filter.select-placeholder',
			default => $this->placeholder,
		};

		$form->addSelect($this->fieldName(), $this->label, $items)
			->setValue($this->value ?? null)
			->checkDefaultValue(false)
			->setPrompt($placeholder);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()] ?? null);
		};
	}


	/**
	 * @param  T[] $cases
	 * @return array<int|string, Html>
	 */
	protected static function convert(array $cases): array
	{
		$items = [];

		foreach ($cases as $case) {
			$option = Html::option($case->name, $case->value);

			if ($case instanceof LabeledEnum) {
				$option = Html::optionEnum($case, true);
			}

			$items[$case->value] = $option;
		}

		return $items;
	}
}
