<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use DateTimeImmutable;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\DataTable\Tools\FormatValue;
use Nette\Forms\Form;
use Throwable;

class DateFilter extends AbstractFilter implements FilterSingle
{
	protected ?DateTimeImmutable $value = null;


	/**
	 * @throws FilterValueInvalidException
	 */
	public function checkValue(mixed $value): ?DateTimeImmutable
	{
		try {
			return FormatValue::dateTime($value);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, DateTimeImmutable::class, $value, $e);
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


	public function getValue(): ?DateTimeImmutable
	{
		return $this->value ?? null;
	}


	public function getValueFrom(): ?DateTimeImmutable
	{
		return $this->value?->modify('midnight');
	}


	public function getValueTo(): ?DateTimeImmutable
	{
		return $this->value?->modify('midnight')?->modify('+1 day');
	}


	public function getValueFormatted(): ?string
	{
		return $this->value?->format('Y-m-d');
	}


	public function attachToForm(Form $form): void
	{
		$input = $form->addDate($this->fieldName(), $this->label)
			->setValue($this->value ?? null);

		$this->applyAttributes($input);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()] ?? null);
		};
	}
}
