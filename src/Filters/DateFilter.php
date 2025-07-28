<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use DateTimeImmutable;
use DateTimeInterface;
use Nette\Application\UI\Form;

class DateFilter extends AbstractFilter
{
	public function attachToForm(Form $form): void
	{
		$form->addDate($this->name, $this->label);

		$form->onSuccess[] = function($form, $data) {
			$this->value = $this->format($data[$this->name] ?? '');
		};
	}


	public function format(mixed $value): string
	{
		if (is_string($value)) {
			$value = new DateTimeImmutable($value);
		}

		if ($value instanceof DateTimeInterface) {
			return $value->format('Y-m-d');
		}

		return '';
	}
}
