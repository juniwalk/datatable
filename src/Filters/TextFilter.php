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

class TextFilter extends AbstractFilter implements FilterSingle
{
	protected ?string $value = null;


	/**
	 * @throws FilterValueInvalidException
	 */
	public function checkValue(mixed $value): ?string
	{
		try {
			return FormatValue::string($value);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, 'string', $value, $e);
		}
	}


	/**
	 * @throws FilterValueInvalidException
	 */
	public function setValue(mixed $value): static
	{
		$this->value = $this->checkValue($value);
		$this->isFiltered = $this->value !== null;

		return $this;
	}


	public function getValue(): ?string
	{
		return $this->value ?? null;
	}


	public function getValueFormatted(): ?string
	{
		return $this->value ?? null;
	}


	public function attachToForm(Form $form): void
	{
		$input = $form->addText($this->fieldName(), $this->label)->setNullable(true)
			->setValue($this->value ?? null);

		$this->applyAttributes($input);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()] ?? null);
		};
	}
}
