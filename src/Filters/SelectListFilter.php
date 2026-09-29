<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Filters\Interfaces\FilterSearchable;
use JuniWalk\DataTable\Traits\SearchHandler;
use JuniWalk\DataTable\Tools\FormatValue;
use Nette\Forms\Form;
use Throwable;

use function array_filter;
use function array_map;

class SelectListFilter extends AbstractFilter implements FilterList, FilterSearchable
{
	use SearchHandler;

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
		$this->items = $this->searchProvider?->findOptions($value) ?? $this->items;

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
		$fieldName = $this->fieldName();
		$input = $form->addMultiSelect($fieldName, $this->label, $this->items)
			->setValue($this->value ?? null)
			->checkDefaultValue(false);

		$this->applyAttributeSearch($input, $fieldName);
		$this->applyAttributes($input);

		if ($this->translateDisabled) {
			$input->setTranslator(null);
		}

		$form->onSuccess[] = function($form, $data) use ($fieldName) {
			$value = $data[$fieldName] ?: $form->getHttpData(Form::DataLine, $fieldName.'[]');
			$this->setValue($value);
		};
	}
}
