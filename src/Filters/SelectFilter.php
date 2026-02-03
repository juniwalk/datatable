<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\DataTable\Tools\FormatValue;
use Nette\Forms\Form;
use Throwable;

class SelectFilter extends AbstractFilter implements FilterSingle
{
	/** @var array<int|string, mixed> */
	protected array $items = [];

	protected int|string|null $value = null;
	protected string|bool $placeholder = true;


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
	public function checkValue(mixed $value): int|string|null
	{
		try {
			return FormatValue::index($value, $this->items);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, 'int|string', $value, $e);
		}
	}


	/**
	 * @param  int|string|null $value
	 * @throws FilterValueInvalidException
	 */
	public function setValue(mixed $value): static
	{
		$this->value = $this->checkValue($value);
		$this->isFiltered = $this->value !== null;

		return $this;
	}


	public function getValue(): int|string|null
	{
		return $this->value ?? null;
	}


	public function getValueFormatted(): int|string|null
	{
		return $this->value ?? null;
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
		$placeholder = match ($this->placeholder) {
			true => 'datatable.filter.select-placeholder',
			default => $this->placeholder,
		};

		$input = $form->addSelect($this->fieldName(), $this->label, $this->items)
			->setValue($this->value ?? null)
			->checkDefaultValue(false)
			->setPrompt($placeholder);

		$this->applyAttributes($input);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()]);
		};
	}
}
