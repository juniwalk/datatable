<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

require __DIR__ . '/../../bootstrap.php';
require './AbstractFilterCase.php';

use JuniWalk\DataTable\Filters;
use Nette\Forms\Controls;
use Tester\Assert;

class TextFilterTest extends AbstractFilterCase
{
	public function testFilter(): void
	{
		$filter = $this->createFilter('name', 'Name', Filters\TextFilter::class);
		$input = $filter->firstInput($this->form);

		Assert::type(Controls\TextInput::class, $input);
		// Assert::exception(
		// 	fn() => $filter->setValue('invalid value'),
		// 	FilterValueInvalidException::class,
		// );

		$this->form->setValues(['name' => 'John']);
		$this->form->fireEvents();

		$value = $filter->getValue();

		Assert::same('John', $filter->getValueFormatted());
		Assert::same('John', $value);
		Assert::type('string', $value);
	}
}

(new TextFilterTest)->run();
