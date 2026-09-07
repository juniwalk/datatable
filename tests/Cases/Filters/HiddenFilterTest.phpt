<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Cases\Filters;

require __DIR__ . '/../../bootstrap.php';

use JuniWalk\DataTable\Columns\TextColumn;
use JuniWalk\DataTable\Filters\HiddenFilter;
use Nette\Forms\Form;
use Tester\Assert;
use Tester\TestCase;

class HiddenFilterTest extends TestCase
{
	public function testFilter(): void
	{
		$column = new TextColumn('Name');
		$column->setParent(null, 'name');

		$filter = new HiddenFilter('Secret');
		$filter->setParent(null, 'secret');
		$filter->setColumns($column);
		$filter->setValue('token123');

		Assert::same('hidden', $filter->getType());
		Assert::same('token123', $filter->getValue());
		Assert::true($filter->isFiltered());

		$form = new Form;
		$filter->attachToForm($form);

		Assert::true(isset($form['secret']));
		Assert::same('token123', $form['secret']->getValue());
	}
}

(new HiddenFilterTest)->run();
