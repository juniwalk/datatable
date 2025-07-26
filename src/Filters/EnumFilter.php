<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use BackedEnum;
use Nette\Application\UI\Form;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Html;

class EnumFilter extends AbstractFilter
{
	/**
	 * @param class-string<BackedEnum> $enum
	 */
	public function __construct(
		protected string $label,
		protected string $enum,
	) {
	}


	public function createInput(Form $form): void
	{
		$items = [];

		foreach ($this->enum::cases() as $case) {
			$option = Html::option($case->name, $case->value);

			if ($case instanceof LabeledEnum) {
				$option = Html::optionEnum($case, true);
			}

			$items[$case->value] = $option;
		}

		$form->addSelect($this->name, $this->label)->setPrompt('Vše…')
			->setItems($items);
	}
}
