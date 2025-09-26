<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Filters;

require __DIR__ . '/../../bootstrap.php';

use DateTimeInterface;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Filters;
use JuniWalk\Tests\Files\AbstractFilterCase;
use Nette\Forms\Controls;
use Tester\Assert;

class DateFilterTest extends AbstractFilterCase
{
	public function testFilter(): void
	{
		$filter = $this->createFilter('birth', 'Birth', Filters\DateFilter::class);
		$input = $filter->firstInput($this->form);

		Assert::type(Controls\DateTimeControl::class, $input);
		Assert::exception(
			fn() => $filter->setValue('not a date'),
			FilterValueInvalidException::class,
		);

		$this->form->setValues(['birth' => '2025-01-01']);
		$this->form->fireEvents();

		$value = $filter->getValue();

		Assert::same('2025-01-01', $filter->getValueFormatted());
		Assert::same('2025-01-01', $value->format('Y-m-d'));
		Assert::type(DateTimeInterface::class, $value);

		Assert::same('2025-01-01 00:00:00', $filter->getValueFrom()?->format('Y-m-d H:i:s'));
		Assert::same('2025-01-02 00:00:00', $filter->getValueTo()?->format('Y-m-d H:i:s'));
	}
}

(new DateFilterTest)->run();
