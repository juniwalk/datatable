<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use DateTimeImmutable;
use Nette\Forms\Form;

class DateTimeRangeFilter extends DateRangeFilter
{
	public function getValueFrom(): ?DateTimeImmutable
	{
		return $this->valueFrom;
	}


	public function getValueTo(): ?DateTimeImmutable
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
			'from' => $this->valueFrom?->format('Y-m-d H:i:s'),
			'to' => $this->valueTo?->format('Y-m-d H:i:s'),
		];
	}


	public function attachToForm(Form $form): void
	{
		$range = $form->addContainer($this->fieldName());
		$range->addDateTime('from', $this->label)->setValue($this->valueFrom);
		$range->addDateTime('to', $this->label)->setValue($this->valueTo);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue([
				'from' => $data[$this->fieldName()]['from'],
				'to' => $data[$this->fieldName()]['to'],
			]);
		};
	}
}
