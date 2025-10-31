<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Filters;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters;
use JuniWalk\Tests\Files\AbstractFilterCase;
use Nette\Forms\Controls;
use Tester\Assert;

class EnumFilterTest extends AbstractFilterCase
{
	public function testFilter(): void
	{
		$filter = $this->createFilter('order', 'Order', Filters\EnumFilter::class, ['enum' => Sort::class]);
		$input = $filter->firstInput($this->form);

		Assert::type(Controls\SelectBox::class, $input);
		Assert::exception(
			fn() => $filter->setValue('invalid value'),
			FilterValueInvalidException::class,
		);

		$this->form->setValues(['order' => Sort::ASC]);
		$this->form->fireEvents();

		$value = $filter->getValue();

		Assert::same(Sort::ASC->value, $filter->getValueFormatted());
		Assert::same(Sort::ASC, $value);
		Assert::type(Sort::class, $value);

		Assert::contains(Sort::ASC, $filter->getItems());
		Assert::same(Sort::class, $filter->getEnumType());
	}


	public function testFilter_Custom_Items(): void
	{
		$filter = $this->createFilter('order', 'Order', Filters\EnumFilter::class, ['enum' => Sort::class])->setItems([Sort::ASC]);
		$filter->firstInput($this->form);

		Assert::exception(
			function() {
				$this->form->setValues(['order' => Sort::DESC]);
				$this->form->fireEvents();
			},
			FilterValueInvalidException::class,
		);

		Assert::null($filter->getValue());
		Assert::notContains(Sort::DESC, $filter->getItems());
	}
}

(new EnumFilterTest)->run();
