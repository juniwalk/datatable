<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters;
use Nette\Http\Helpers;
use Nette\Forms\Form;
use Tester\Assert;
use Tester\TestCase;

/**
 * @phpstan-import-type FilterStruct from Filter
 */
abstract class AbstractFilterCase extends TestCase
{
	protected Form $form;


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


	/**
	 * @param  class-string<FilterStruct> $class
	 * @param  mixed[] $args
	 * @return FilterStruct
	 */
	protected function createFilter(string $name, string $label, string $class, array $args = []): Filter
	{
		$filter = new $class($label, ...$args);
		$filter->setParent(null, $name);
		$filter->attachToForm($this->form);

		return $filter;
	}
}
