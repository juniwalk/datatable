<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Table;

class TestTable extends Table
{
	public function __construct(
		bool $interactive = true,
	) {
		$this->setCaption('Test Table');
		$this->setInteractive($interactive);
	}


	protected function createModel(): mixed
	{
		// ? Data from ItemsData constant in bootstrap.php
		return ItemsData;
	}


	protected function createTable(): void
	{
		$this->addColumnOrder('order', 'Order')->setSortable(true);
		$this->addColumnText('name', 'Name');
		$this->addColumnNumber('height', 'Height');
		$this->addColumnDropdown('align', 'Align', Align::cases())->setLink('this');
		$this->addColumnNumber('id', '#')->setSortable(true);
	}


	protected function createFilters(): void
	{
		$this->addFilterText('name', 'Name');
		$this->addFilterEnumList('align', 'Align', Align::class);
	}
}
