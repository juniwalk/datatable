<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use DateTimeImmutable;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterRange;
use JuniWalk\DataTable\Tools\FormatValue;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Form;
use Throwable;

class DateRangeFilter extends AbstractFilter implements FilterRange
{
	protected ?DateTimeImmutable $valueFrom = null;
	protected ?DateTimeImmutable $valueTo = null;


	/**
	 * @param  array{from?: mixed, to?: mixed} $value
	 * @return array{from: ?DateTimeImmutable, to: ?DateTimeImmutable}
	 * @throws FilterValueInvalidException
	 */
	public function checkValue(?array $value): array
	{
		$result = ['from' => null, 'to' => null];

		try {
			$result['from'] = FormatValue::dateTime($value['from'] ?? null);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, DateTimeImmutable::class, $value['from'] ?? null, $e);
		}

		try {
			$result['to'] = FormatValue::dateTime($value['to'] ?? null);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, DateTimeImmutable::class, $value['to'] ?? null, $e);
		}

		return $result;
	}


	/**
	 * @param  array{from?: mixed, to?: mixed} $value
	 * @throws FilterValueInvalidException
	 */
	public function setValue(?array $value): static
	{
		$value = $this->checkValue($value);

		$this->valueFrom = $value['from'];
		$this->valueTo = $value['to'];
		$this->isFiltered = !empty($this->valueFrom) || !empty($this->valueTo);

		return $this;
	}


	/**
	 * @return array{from: ?DateTimeImmutable, to: ?DateTimeImmutable}
	 */
	public function getValue(): ?array
	{
		return [
			'from' => $this->getValueFrom(),
			'to' => $this->getValueTo(),
		];
	}


	public function getValueFrom(): ?DateTimeImmutable
	{
		return $this->valueFrom?->modify('midnight');
	}


	public function getValueTo(): ?DateTimeImmutable
	{
		return $this->valueTo?->modify('midnight');
	}


	/**
	 * @return array{from: ?string, to: ?string}
	 */
	public function getValueFormatted(): ?array
	{
		// ? With || it does not allow partial filtering
		if (!$this->valueFrom && !$this->valueTo) {
			return null;
		}

		return [
			'from' => $this->valueFrom?->format('Y-m-d'),
			'to' => $this->valueTo?->format('Y-m-d'),
		];
	}


	public function attachToForm(Form $form): void
	{
		$range = $form->addContainer($this->fieldName());
		$inputFrom = $range->addDate('from', $this->label)->setValue($this->valueFrom);
		$inputTo = $range->addDate('to', $this->label)->setValue($this->valueTo);

		$this->applyAttributes($inputFrom, $inputTo);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue([
				'from' => $data[$this->fieldName()]['from'],
				'to' => $data[$this->fieldName()]['to'],
			]);
		};
	}


	public function firstInput(Form $form): IComponent
	{
		return $form->getComponent($this->fieldName())->getComponent('from');
	}
}
