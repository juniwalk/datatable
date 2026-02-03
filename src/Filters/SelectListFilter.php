<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Tools\FormatValue;
use Nette\Forms\Form;
use Throwable;

class SelectListFilter extends AbstractFilter implements FilterList
{
	/** @var array<int|string, mixed> */
	protected array $items = [];

	/** @var array<int|string> */
	protected ?array $value = null;


	/**
	 * @param  mixed[] $value
	 * @return array<int|string>
	 * @throws FilterValueInvalidException
	 */
	public function checkValue(?array $value): ?array
	{
		try {
			$result = array_filter(
				array_map(fn($x) => FormatValue::index($x, $this->items), $value ?? []),
			);

			return $result ?: null;

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, 'array<int|string>', $value, $e);
		}
	}


	/**
	 * @param  mixed[] $value
	 * @throws FilterValueInvalidException
	 */
	public function setValue(?array $value): static
	{
		$this->value = $this->checkValue($value);
		$this->isFiltered = $this->value !== null;

		return $this;
	}


	/**
	 * @return null|array<int|string>
	 */
	public function getValue(): ?array
	{
		return $this->value ?? null;
	}


	/**
	 * @return null|array<int|string>
	 */
	public function getValueFormatted(): ?array
	{
		if (empty($this->value)) {
			return null;
		}

		return $this->value;
	}


	/**
	 * @param array<int|string, mixed> $items
	 */
	public function setItems(array $items): static
	{
		$this->items = $items;
		return $this;
	}


	/**
	 * @return array<int|string, mixed>
	 */
	public function getItems(): array
	{
		return $this->items;
	}


	public function attachToForm(Form $form): void
	{
		$input = $form->addMultiSelect($this->fieldName(), $this->label, $this->items)
			->setValue($this->value ?? null)
			->checkDefaultValue(false);

		$this->applyAttributes($input);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue((array) $data[$this->fieldName()]);
		};
	}
}
