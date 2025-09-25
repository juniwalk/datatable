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

class NumberRangeFilterTest extends AbstractFilterCase
{
	public function testFilter(): void
	{
		$filter = $this->createFilter('age', 'Age', Filters\NumberRangeFilter::class);
		$input = $filter->firstInput($this->form);

		Assert::type(Controls\TextInput::class, $input);
		// Assert::exception(
		// 	fn() => $filter->setValue(['from' => 'not a number', 'to' => '']),
		// 	FilterValueInvalidException::class,
		// );

		$this->form->setValues(['age' => ['from' => 18, 'to' => 35]]);
		$this->form->fireEvents();

		$value = $filter->getValue();

		Assert::same(['from' => '18', 'to' => '35'], $filter->getValueFormatted());
		Assert::same(['from' => 18, 'to' => 35], $value);

		Assert::type('int', $value['from']);
		Assert::type('int', $value['to']);

		Assert::same(18, $filter->getValueFrom());
		Assert::same(35, $filter->getValueTo());
	}
}

(new NumberRangeFilterTest)->run();
