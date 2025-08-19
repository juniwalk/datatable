<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use DateTimeImmutable;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Tools\FormatValue;
use Nette\Application\UI\Form;
use Throwable;

class DateFilter extends AbstractFilter
{
	protected ?DateTimeImmutable $value;


	/**
	 * @throws FilterValueInvalidException
	 */
	public function setValue(mixed $value): static
	{
		try {
			$this->value = FormatValue::dateTime($value);
			$this->isFiltered = !empty($this->value);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, DateTimeImmutable::class, $value, $e);
		}

		return $this;
	}


	/**
	 * @return ?DateTimeImmutable
	 */
	public function getValue(): mixed
	{
		return $this->value ?? null;
	}


	public function getValueFormatted(): int|string|float|null
	{
		return $this->value?->format('Y-m-d');
	}


	public function attachToForm(Form $form): void
	{
		$form->addDate($this->fieldName(), $this->label)
			->setValue($this->value ?? null);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()] ?? null);
		};
	}
}
