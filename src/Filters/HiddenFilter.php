<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use Nette\Forms\Form;

class HiddenFilter extends TextFilter
{
	public function attachToForm(Form $form): void
	{
		$input = $form->addHidden($this->fieldName(), $this->label)->setNullable(true)
			->setValue($this->value ?? null);

		$this->applyAttributes($input);

		$form->onSuccess[] = function($form, $data) {
			$this->setValue($data[$this->fieldName()] ?? null);
		};
	}
}
