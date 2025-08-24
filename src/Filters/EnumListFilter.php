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
use Nette\Application\UI\Form;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Html;
use Throwable;

/**
 * @template T of BackedEnum
 */
class EnumListFilter extends AbstractFilter implements FilterList
{
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
	 * @param  null|array<int|string|T> $value
	 * @throws FilterValueInvalidException
	 */
	public function setValue(?array $value): static
	{
		try {
			$this->value = array_filter(
				array_map(fn($x) => FormatValue::enum($x, $this->enum), $value ?? [])
			);;

			$this->value = $this->value ?: null;
			$this->isFiltered = !empty($this->value);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, $this->enum.'[]', $value, $e);
		}

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
		$form->addMultiSelect($this->fieldName(), $this->label, $this->getItems())
			->setValue($this->value ?? null);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue((array) $data[$this->fieldName()]);
		};
	}
}
