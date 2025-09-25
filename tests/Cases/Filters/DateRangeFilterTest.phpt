<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

require __DIR__ . '/../../bootstrap.php';
require './AbstractFilterCase.php';

use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters;
use Nette\Forms\Controls;
use Tester\Assert;

class DateRangeFilterTest extends AbstractFilterCase
{
	public function testFilter(): void
	{
		$filter = $this->createFilter('birth', 'Birth', Filters\DateRangeFilter::class);
		$input = $filter->firstInput($this->form);

		Assert::type(Controls\DateTimeControl::class, $input);
		Assert::exception(
			fn() => $filter->setValue(['from' => 'not a date', 'to' => '']),
			FilterValueInvalidException::class,
		);

		$this->form->setValues(['birth' => ['from' => '2025-01-01', 'to' => '2025-06-01']]);
		$this->form->fireEvents();

		$value = $filter->getValue();

		Assert::same(['from' => '2025-01-01', 'to' => '2025-06-01'], $filter->getValueFormatted());
		Assert::same(['from' => '2025-01-01', 'to' => '2025-06-01'], [
			'from' => $value['from']->format('Y-m-d'),
			'to' => $value['to']->format('Y-m-d'),
		]);

		Assert::type(DateTimeInterface::class, $value['from']);
		Assert::type(DateTimeInterface::class, $value['to']);

		Assert::same('2025-01-01 00:00:00', $filter->getValueFrom()?->format('Y-m-d H:i:s'));
		Assert::same('2025-06-01 00:00:00', $filter->getValueTo()?->format('Y-m-d H:i:s'));
	}
}

(new DateRangeFilterTest)->run();
