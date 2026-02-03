<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters\Interfaces\FilterRange;
use JuniWalk\DataTable\Tools\FormatValue;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Form;
use Throwable;

class NumberRangeFilter extends AbstractFilter implements FilterRange
{
	protected int|float|null $valueFrom = null;
	protected int|float|null $valueTo = null;

	protected ?int $precission = null;


	public function setPrecission(?int $precission): static
	{
		$this->precission = $precission;
		return $this;
	}


	/**
	 * @param  array{from?: mixed, to?: mixed} $value
	 * @return array{from: int|float|null, to: int|float|null}
	 * @throws FilterValueInvalidException
	 */
	public function checkValue(?array $value): array
	{
		$result = ['from' => null, 'to' => null];

		try {
			$result['from'] = FormatValue::number($value['from'] ?? null, $this->precission);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, 'int|float', $value['from'] ?? null, $e);
		}

		try {
			$result['to'] = FormatValue::number($value['to'] ?? null, $this->precission);

		} catch (Throwable $e) {
			throw FilterValueInvalidException::fromFilter($this, 'int|float', $value['to'] ?? null, $e);
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
	 * @return array{from: int|float|null, to: int|float|null}
	 */
	public function getValue(): ?array
	{
		return [
			'from' => $this->getValueFrom(),
			'to' => $this->getValueTo(),
		];
	}


	public function getValueFrom(): int|float|null
	{
		return $this->valueFrom;
	}


	public function getValueTo(): int|float|null
	{
		return $this->valueTo;
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
			'from' => FormatValue::string($this->valueFrom),
			'to' => FormatValue::string($this->valueTo),
		];
	}


	public function attachToForm(Form $form): void
	{
		$range = $form->addContainer($this->fieldName());
		$inputFrom = $range->addFloat('from', $this->label)->setValue($this->valueFrom ?? null);
		$inputTo = $range->addFloat('to', $this->label)->setValue($this->valueTo ?? null);

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
