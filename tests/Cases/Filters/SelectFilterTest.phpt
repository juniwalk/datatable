<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Filters;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters;
use JuniWalk\Tests\Files\AbstractFilterCase;
use Nette\Forms\Controls;
use Tester\Assert;

class SelectFilterTest extends AbstractFilterCase
{
	public function testFilter(): void
	{
		$filter = new Filters\SelectFilter('Field');
		$filter->setParent(null, 'field');
		$filter->setItems([
			'name' => 'Name',
			'age' => 'Age',
			'birth' => 'Birth',
		]);

		$filter->attachToForm($this->form);
		$input = $filter->firstInput($this->form);

		Assert::type(Controls\SelectBox::class, $input);
		Assert::exception(
			fn() => $filter->setValue('invalid value'),
			FilterValueInvalidException::class,
		);

		$this->form->setValues(['field' => 'name']);
		$this->form->fireEvents();

		$value = $filter->getValue();

		Assert::same('name', $filter->getValueFormatted());
		Assert::same('name', $value);
		Assert::type('string', $value);

		Assert::hasKey('name', $filter->getItems());
	}
}

(new SelectFilterTest)->run();
