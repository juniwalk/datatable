<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use BackedEnum;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Tools\FormatValue;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Html;
use Nette\Forms\Form;
use Throwable;

/**
 * @template T of BackedEnum
 */
class EnumListFilter extends AbstractFilter implements FilterList
{
	/** @var array<int|string, T> */
	protected array $items;

	/** @var T[] */
	protected ?array $value = null;


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


	/**
	 * @param  array<int|string|T> $value
	 * @return T[]
	 * @throws FilterValueInvalidException
	 */
	public function checkValue(?array $value): ?array
	{
		$items = $this->getItems();

		try {
			$result = array_filter(
				array_map(fn($x) => FormatValue::enum($x, $this->enum), $value ?? []),
				fn($x) => in_array($x, $items),
			);

			return $result ?: null;

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, $this->enum.'[]', $value, $e);
		}
	}


	/**
	 * @param  array<int|string|T> $value
	 * @throws FilterValueInvalidException
	 */
	public function setValue(?array $value): static
	{
		$this->value = $this->checkValue($value);
		$this->isFiltered = $this->value !== null;

		return $this;
	}


	/**
	 * @return null|T[]
	 */
	public function getValue(): ?array
	{
		return $this->value ?? null;
	}


	/**
	 * @return null|array<value-of<T>>
	 */
	public function getValueFormatted(): ?array
	{
		if (empty($this->value)) {
			return null;
		}

		return array_map(fn($x) => $x->value, $this->value);
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

		$form->addMultiSelect($this->fieldName(), $this->label, $items)
			->setValue($this->value ?? null)
			->checkDefaultValue(false);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue((array) $data[$this->fieldName()]);
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
