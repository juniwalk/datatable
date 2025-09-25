<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

require __DIR__ . '/../bootstrap.php';

use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Exceptions\FilterInvalidException;
use JuniWalk\DataTable\Exceptions\FilterValueInvalidException;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters;
use JuniWalk\DataTable\Filters\AbstractFilter;
use Nette\Http\Helpers;
use Nette\Forms\Controls;
use Nette\Forms\Form;
use Tester\Assert;
use Tester\TestCase;

class FiltersTest extends TestCase
{
	private Form $form;


	public function setUp(): void
	{
		// ? Taken from Nette\Forms tests to allow Form::fireEvents to work
		$_COOKIE[Helpers::StrictCookieName] = '1';
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$this->form = new Form;
	}


	public function testBasics(): void
	{
		$column = new TextColumn('Name');
		$column->setParent(null, 'name');

		$filter = $this->createFilter('name', 'Name', Filters\TextFilter::class);
		$filter->setColumns($column);

		Assert::type(TextColumn::class, $filter->getColumns()['name']);
		Assert::true($filter->hasColumn('name'));

		Assert::same('name', $filter->fieldName());
		Assert::same('text', $filter->getType());
		Assert::false($filter->hasCondition());
		Assert::false($filter->applyCondition(null));
		Assert::false($filter->isFiltered());

		$filter->setCondition(fn() => true);
		$filter->setValue('John');

		Assert::true($filter->hasCondition());
		Assert::true($filter->applyCondition(null));
		Assert::true($filter->isFiltered());
	}


	public function testDateFilter(): void
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


	public function testDateRangeFilter(): void
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


	public function testEnumFilter(): void
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

		Assert::hasKey(Sort::ASC->value, $filter->getItems());
		Assert::same(Sort::class, $filter->getEnumType());
	}


	public function testEnumListFilter(): void
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

		Assert::hasKey(Sort::ASC->value, $filter->getItems());
		Assert::same(Sort::class, $filter->getEnumType());
	}


	public function testNumberRangeFilter(): void
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


	public function testTextFilter(): void
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


	public function testInvalidFilter(): void
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


	/**
	 * @param class-string<Filter> $class
	 * @param mixed[] $args
	 */
	private function createFilter(string $name, string $label, string $class, array $args = []): Filter
	{
		$filter = new $class($label, ...$args);
		$filter->setParent(null, $name);
		$filter->attachToForm($this->form);

		return $filter;
	}
}

(new FiltersTest)->run();
