<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use Nette\Application\UI\Form;

class DateFilter extends AbstractFilter
{
	public function createInput(Form $form): void
	{
		$form->addDate($this->name, $this->label);
	}
}
