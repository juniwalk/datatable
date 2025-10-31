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

class EnumListFilterTest extends AbstractFilterCase
{
	public function testFilter(): void
	{
		$filter = $this->createFilter('order', 'Order', Filters\EnumListFilter::class, ['enum' => Sort::class]);
		$input = $filter->firstInput($this->form);

		Assert::type(Controls\MultiSelectBox::class, $input);
		Assert::exception(
			fn() => $filter->setValue(['asc', 'invalid value']),
			FilterValueInvalidException::class,
		);

		$this->form->setValues(['order' => [Sort::ASC, Sort::DESC]]);
		$this->form->fireEvents();

		$value = $filter->getValue();

		Assert::contains(Sort::ASC->value, $filter->getValueFormatted());
		Assert::contains(Sort::ASC, $value);
		Assert::type(Sort::class, $value[0]);

		Assert::contains(Sort::ASC, $filter->getItems());
		Assert::same(Sort::class, $filter->getEnumType());
	}


	public function testFilter_Custom_Items(): void
	{
		$filter = $this->createFilter('order', 'Order', Filters\EnumListFilter::class, ['enum' => Sort::class])->setItems([Sort::ASC]);
		$filter->firstInput($this->form);

		Assert::noError(function() {
			$this->form->setValues(['order' => [Sort::ASC, Sort::DESC]]);
			$this->form->fireEvents();
		});

		$value = $filter->getValue();

		Assert::contains(Sort::ASC, $value);
		Assert::notContains(Sort::DESC, $filter->getItems());
	}
}

(new EnumListFilterTest)->run();
