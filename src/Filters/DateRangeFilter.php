<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use DateTimeImmutable;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\FilterRanged;
use JuniWalk\DataTable\Tools\FormatValue;
use Nette\Application\UI\Form;
use Throwable;

class DateRangeFilter extends AbstractFilter implements FilterRanged
{
	protected ?DateTimeImmutable $valueFrom;
	protected ?DateTimeImmutable $valueTo;


	/**
	 * @param  array{from: mixed, to: mixed} $value
	 * @throws FilterValueInvalidException
	 */
	public function setValue(mixed $value): static
	{
		try {
			$this->valueFrom = FormatValue::dateTime($value['from'] ?? null);
			$this->valueTo = FormatValue::dateTime($value['to'] ?? null);
			$this->isFiltered = !empty($this->valueFrom) && !empty($this->valueTo);

		} catch (Throwable $e) {
			// todo: cannot use $value
			throw FilterValueInvalidException::fromFilter($this, DateTimeImmutable::class, $value, $e);
		}

		return $this;
	}


	/**
	 * @return array{from: ?DateTimeImmutable, to: ?DateTimeImmutable}
	 */
	public function getValue(): mixed
	{
		return [
			'from' => $this->valueFrom ?? null,
			'to' => $this->valueTo ?? null,
		];
	}


	/**
	 * @return ?DateTimeImmutable
	 */
	public function getValueFrom(): mixed
	{
		return $this->valueFrom;
	}


	/**
	 * @return ?DateTimeImmutable
	 */
	public function getValueTo(): mixed
	{
		return $this->valueTo;
	}


	/**
	 * @return array{from: ?string, to: ?string}
	 */
	public function getValueFormatted(): mixed
	{
		return [
			'from' => $this->valueFrom?->format('Y-m-d'),
			'to' => $this->valueTo?->format('Y-m-d'),
		];
	}


	public function attachToForm(Form $form): void
	{
		$range = $form->addContainer($this->fieldName());
		$range->addDate('from', $this->label)
			->setValue($this->valueFrom ?? null);
		$range->addDate('to', $this->label)
			->setValue($this->valueTo ?? null);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()] ?? []);
		};
	}
}
