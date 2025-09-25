<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

require __DIR__ . '/../../bootstrap.php';
require './AbstractFilterCase.php';

use JuniWalk\DataTable\Exceptions\FilterInvalidException;
use JuniWalk\DataTable\Filters\AbstractFilter;
use Nette\Forms\Form;
use Tester\Assert;

class InvalidFilterTest extends AbstractFilterCase
{
	public function testFilter(): void
	{
		$filter = new class('Filter') extends AbstractFilter {
			public function attachToForm(Form $form): void {}
			public function setValue(string $value): void {
				$this->isFiltered = !empty($value);
			}
		};

		$filter->setCondition(fn() => true);
		$filter->setValue('John');

		Assert::true($filter->isFiltered());
		Assert::exception(
			fn() => $filter->applyCondition([]),
			FilterInvalidException::class
		);
	}
}

(new InvalidFilterTest)->run();
