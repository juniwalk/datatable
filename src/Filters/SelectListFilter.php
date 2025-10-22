<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use Nette\Forms\Form;
use Throwable;

class SelectListFilter extends AbstractFilter implements FilterList
{
	/** @var array<int|string, mixed> */
	protected array $items = [];

	/** @var array<int|string> */
	protected ?array $value = null;


	/**
	 * @param  null|array<int|string> $value
	 * @throws FilterValueInvalidException
	 */
	public function setValue(?array $value): static
	{
		try {
			$this->value = array_filter($value ?? [], fn($x) => isset($this->items[$x])) ?: null;
			$this->isFiltered = $this->value !== null;

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, 'array<int|string>', $value, $e);
		}

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
		$form->addMultiSelect($this->fieldName(), $this->label, $this->items)
			->setValue($this->value ?? null)
			->checkDefaultValue(false);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue((array) $data[$this->fieldName()]);
		};
	}
}
