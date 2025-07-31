<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use Nette\Application\UI\Form;

class TextFilter extends AbstractFilter
{
	public function attachToForm(Form $form): void
	{
		$form->addText($this->name, $this->label)->setNullable(true);

		$form->onSuccess[] = function($form, $data) {
			$this->value = $this->format($data[$this->name] ?? null);
		};
	}
}
