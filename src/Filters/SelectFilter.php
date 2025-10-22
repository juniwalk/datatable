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
use OutOfBoundsException;
use Throwable;

class SelectFilter extends AbstractFilter implements FilterSingle
{
	/** @var array<int|string, mixed> */
	protected array $items = [];

	protected ?string $value = null;
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
	 * @param  int|string|null $value
	 * @throws FilterValueInvalidException
	 */
	public function setValue(mixed $value): static
	{
		try {
			if ($value && !isset($this->items[$value])) {
				throw new OutOfBoundsException('Value "'.$value.'" is not in items list.');
			}

			$this->value = FormatValue::string($value);
			$this->isFiltered = $this->value !== null;

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, 'int|string', $value, $e);
		}

		return $this;
	}


	/**
	 * @return ?string
	 */
	public function getValue(): mixed
	{
		return $this->value ?? null;
	}


	/**
	 * @return ?string
	 */
	public function getValueFormatted(): mixed
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
		$placeholder = $this->placeholder;

		if ($placeholder === true) {
			$placeholder = 'datatable.filter.select-placeholder';
		}

		$form->addSelect($this->fieldName(), $this->label, $this->items)
			->setValue($this->value ?? null)
			->checkDefaultValue(false)
			->setPrompt($placeholder);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()]);
		};
	}
}
