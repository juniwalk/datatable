<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Filters;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Filters;
use JuniWalk\Tests\Files\AbstractFilterCase;
use Nette\Forms\Controls;
use Tester\Assert;

class SelectListFilterTest extends AbstractFilterCase
{
	public function testFilter(): void
	{
		$filter = new Filters\SelectListFilter('Field');
		$filter->setParent(null, 'field');
		$filter->setItems([
			'name' => 'Name',
			'age' => 'Age',
			'birth' => 'Birth',
		]);

		$filter->attachToForm($this->form);
		$input = $filter->firstInput($this->form);

		Assert::type(Controls\MultiSelectBox::class, $input);

		$this->form->setValues(['field' => ['name', 'age']]);
		$this->form->fireEvents();

		$value = $filter->getValue();

		Assert::contains('name', $filter->getValueFormatted());
		Assert::contains('name', $value);
		Assert::type('string', $value[0]);

		Assert::hasKey('name', $filter->getItems());
	}
}

(new SelectListFilterTest)->run();
