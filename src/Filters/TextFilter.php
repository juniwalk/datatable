<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\DataTable\Tools\FormatValue;
use Nette\Application\UI\Form;
use Throwable;

class TextFilter extends AbstractFilter implements FilterSingle
{
	protected ?string $value;


	/**
	 * @throws FilterValueInvalidException
	 */
	public function setValue(mixed $value): static
	{
		try {
			$this->value = FormatValue::string($value) ?: null;
			$this->isFiltered = !empty($this->value);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, 'string', $value, $e);
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
	 * @return string|null
	 */
	public function getValueFormatted(): mixed
	{
		return $this->value ?? null;
	}


	public function attachToForm(Form $form): void
	{
		$form->addText($this->fieldName(), $this->label)->setNullable(true)
			->setValue($this->value ?? null);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()] ?? null);
		};
	}
}
