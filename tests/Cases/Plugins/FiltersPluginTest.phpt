<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Plugins;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Filters;
use JuniWalk\DataTable\Enums\Option;
use JuniWalk\DataTable\Enums\Sort;
use JuniWalk\DataTable\Exceptions\FilterNotFoundException;
use JuniWalk\Tests\Files\TemplateFactory;
use JuniWalk\Tests\Files\TestPresenter;
use Nette\Application\AbortException;
use Nette\Http\Helpers;
use Nette\Forms\Controls;
use Tester\Assert;
use Tester\TestCase;

class FiltersPluginTest extends TestCase
{
	public function testFilters(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->addFilterDate('date', 'Date');
		$table->addFilterDateRange('dateRange', 'Date Range');
		$table->addFilterEnum('enum', 'Enum', Sort::class);
		$table->addFilterEnumList('enumList', 'Enum List', Sort::class);
		$table->addFilterNumberRange('numberRange', 'Number Range');
		$table->addFilterSelect('select', 'Select', []);
		$table->addFilterSelectList('selectList', 'Select List', []);
		$table->addFilterText('text', 'Text');

		Assert::type(Filters\DateFilter::class, $table->getFilter('date'));
		Assert::type(Filters\DateRangeFilter::class, $table->getFilter('dateRange'));
		Assert::type(Filters\EnumFilter::class, $table->getFilter('enum'));
		Assert::type(Filters\EnumListFilter::class, $table->getFilter('enumList'));
		Assert::type(Filters\NumberRangeFilter::class, $table->getFilter('numberRange'));
		Assert::type(Filters\SelectFilter::class, $table->getFilter('select'));
		Assert::type(Filters\SelectListFilter::class, $table->getFilter('selectList'));
		Assert::type(Filters\TextFilter::class, $table->getFilter('text'));

		$table->removeFilter('numberRange');
		$filters = $table->getFilters();

		Assert::hasNotKey('numberRange', $filters);
		Assert::null($table->getFilter('numberRange', false));
		Assert::exception(
			fn() => $table->getFilter('numberRange'),
			FilterNotFoundException::class,
		);
	}


	public function testFilters_Showing(): void
	{
		$table = (new TestPresenter)->getComponent('tableWithSource');
		$table->clearRememberedState();

		Assert::null($table->isFilterShown());
		Assert::true($table->isAutoSubmit());
		Assert::false($table->shouldShowFilters());

		$table->setDefaultFilter(['name' => 'John Doe']);
		Assert::true($table->shouldShowFilters());

		$table->setAutoSubmit(false)->setFilterShown(false);
		Assert::false($table->isFilterShown());
		Assert::false($table->isAutoSubmit());
		Assert::false($table->shouldShowFilters());

		$table->setDefaultFilter([])->setFilterShown(null);
		$table->filter = ['name' => 'John Doe'];
		Assert::true($table->shouldShowFilters());
	}


	public function testFilters_Default(): void
	{
		$table = (new TestPresenter)->getComponent('tableWithSource');
		$table->setDefaultFilter(['name' => 'John Doe']);
		$table->clearRememberedState();

		Assert::false($table->isDefaultFilter());
		Assert::exception(
			fn() => $table->setDefaultFilter(['unknown' => '']),
			FilterNotFoundException::class,
		);

		$table->filter['name'] = 'John Doe';
		Assert::true($table->isDefaultFilter());
		Assert::same(['name' => 'John Doe'], $table->getCurrentFilter());

		Assert::with($table, function() {
			$this->setOption(Option::IsFiltered, true);
			$this->filter = [];

			Assert::same([], $this->getCurrentFilter());
		});
	}


	public function testFilters_Render(): void
	{
		$table = (new TestPresenter)->getComponent('tableWithSource');
		$table->setDefaultFilter(['name' => 'John Doe']);
		$table->clearRememberedState();

		$template = (new TemplateFactory)->createTemplate();
		Assert::with($table, fn() => $this->onRenderFilters($template));

		Assert::true($template->autoSubmit ?? null);
		Assert::type('array', $template->filters ?? null);

		Assert::same('John Doe', $table->getFilter('name')->getValue());
		Assert::true($table->getColumn('name')->isFiltered());

		Assert::noError(fn() => $table->getToolbarAction('__filter_toggle'));
		Assert::noError(fn() => $table->getToolbarAction('__filter_clear'));
	}


	public function testFilters_Component(): void
	{
		// ? Taken from Nette\Forms tests to allow Form::fireEvents to work
		$_COOKIE[Helpers::StrictCookieName] = '1';
		$_SERVER['REQUEST_METHOD'] = 'POST';

		$table = (new TestPresenter)->getComponent('table');
		$table->addFilterDate('date', 'Date');
		$table->addFilterEnum('enum', 'Enum', Sort::class);
		$table->addFilterText('text', 'Text');

		$form = $table->getComponent('filterForm');
		$token = $form[$form::ProtectorId];

		Assert::type(Controls\DateTimeControl::class, $form['date']);
		Assert::type(Controls\SelectBox::class, $form['enum']);
		Assert::type(Controls\TextInput::class, $form['text']);

		// ? Set token from CSRF protection into value property of Control
		Assert::with($token, fn() => $this->value = $this->getControl()->value);

		$form->setValues(['text' => 'Jane Doe']);
		$form->setSubmittedBy($form['__submit']);

		Assert::exception(
			fn() => $form->fireEvents(),
			AbortException::class,
		);

		Assert::same('Jane Doe', $table->getFilter('text')->getValue());
		Assert::count(0, $form->getErrors());
	}
}

(new FiltersPluginTest)->run();
