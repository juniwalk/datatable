<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Plugins;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Columns;
use JuniWalk\DataTable\Exceptions\ColumnNotFoundException;
use JuniWalk\Tests\Files\TemplateFactory;
use JuniWalk\Tests\Files\TestPresenter;
use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;

class ColumnsPluginTest extends TestCase
{
	public function testColumns(): void
	{
		$table = (new TestPresenter)->getComponent('table');
		$table->addColumnText('text', 'Test');
		$table->addColumnLink('link', 'Link');
		$table->addColumnEnum('enum', 'Enum');
		$table->addColumnNumber('number', 'Number');
		$table->addColumnDate('date', 'Date');
		$table->addColumnOrder('order', 'Order');
		$table->addColumnDropdown('dropdown', 'Dropdown', [
			'active' => 'Active',
			'inactive' => 'InActive',
		]);

		Assert::type(Columns\TextColumn::class, $table->getColumn('text'));
		Assert::type(Columns\LinkColumn::class, $table->getColumn('link'));
		Assert::type(Columns\EnumColumn::class, $table->getColumn('enum'));
		Assert::type(Columns\NumberColumn::class, $table->getColumn('number'));
		Assert::type(Columns\DateColumn::class, $table->getColumn('date'));
		Assert::type(Columns\DropdownColumn::class, $table->getColumn('dropdown'));
		Assert::type(Columns\OrderColumn::class, $table->getColumn('order'));

		$table->removeColumn('dropdown');
		$columns = $table->getColumns();

		Assert::hasNotKey('dropdown', $columns);
		Assert::null($table->getColumn('dropdown', false));
		Assert::exception(
			fn() => $table->getColumn('dropdown'),
			ColumnNotFoundException::class,
		);
	}


	public function testColumns_Hideable(): void
	{
		$table = (new TestPresenter)->getComponent('tableWithSource');
		Assert::false($table->isColumnsHideable());

		$table->setColumnsHideable(true);
		Assert::true($table->isColumnsHideable());
		Assert::true($table->isColumnHideable('name'));
	}


	public function testColumns_Hideable_ShowToggle(): void
	{
		$table = (new TestPresenter)->getComponent('tableWithSource');
		$table->getColumn('name')->setDefaultHide(true);
		$table->setColumnsHideable(true);

		Assert::exception(
			fn() => $table->handleShowToggle('unknown'),
			ColumnNotFoundException::class,
		);

		Assert::true($table->getColumn('name')->isHidden());

		Assert::exception(
			fn() => $table->handleShowToggle('name'),
			AbortException::class,
		);

		$template = (new TemplateFactory)->createTemplate();
		Assert::with($table, fn() => $this->onRenderColumns($template));

		Assert::false($table->getColumn('id')->isHidden());
		Assert::false($table->getColumn('name')->isHidden());
	}


	public function testColumns_Hideable_ShowDefault(): void
	{
		$table = (new TestPresenter)->getComponent('tableWithSource');
		$table->getColumn('name')->setDefaultHide(true);
		$table->setColumnsHideable(true);

		Assert::exception(
			fn() => $table->handleShowToggle('name'),
			AbortException::class,
		);

		Assert::false($table->getColumn('id')->isHidden());
		Assert::true($table->getColumn('name')->isHidden());

		Assert::exception(
			fn() => $table->handleShowDefault(),
			AbortException::class,
		);

		$template = (new TemplateFactory)->createTemplate();
		Assert::with($table, fn() => $this->onRenderColumns($template));

		Assert::false($table->getColumn('id')->isHidden());
		Assert::true($table->getColumn('name')->isHidden());
	}


	public function testColumns_Hideable_ShowAll(): void
	{
		$table = (new TestPresenter)->getComponent('tableWithSource');
		$table->getColumn('name')->setDefaultHide(true);
		$table->setColumnsHideable(true);

		Assert::exception(
			fn() => $table->handleShowToggle('name'),
			AbortException::class,
		);

		Assert::false($table->getColumn('id')->isHidden());
		Assert::true($table->getColumn('name')->isHidden());

		Assert::exception(
			fn() => $table->handleShowAll(),
			AbortException::class,
		);

		$template = (new TemplateFactory)->createTemplate();
		Assert::with($table, fn() => $this->onRenderColumns($template));

		Assert::false($table->getColumn('id')->isHidden());
		Assert::false($table->getColumn('name')->isHidden());
	}


	public function testColumns_Render(): void
	{
		$table = (new TestPresenter)->getComponent('tableWithSource');
		$table->getColumn('name')->setDefaultHide(true);
		$table->setColumnsHideable(true);

		Assert::exception(
			fn() => $table->handleShowDefault(),
			AbortException::class,
		);

		$template = (new TemplateFactory)->createTemplate();
		Assert::with($table, fn() => $this->onRenderColumns($template));

		Assert::type('array', $template->columns ?? null);
		Assert::noError(fn() => $table->getToolbarAction('__settings'));
	}
}

(new ColumnsPluginTest)->run();
