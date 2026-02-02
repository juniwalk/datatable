<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use JuniWalk\DataTable\Table;

class TestTable extends Table
{
	protected function createModel(): mixed
	{
		return ItemsData;
	}


	protected function createTable(): void
	{
		$this->addColumnOrder('order', 'Order')->setSortable(true);
		$this->addColumnText('name', 'Name');
		$this->addColumnNumber('height', 'Height');
		$this->addColumnNumber('id', '#')->setSortable(true);

		$this->addFilterText('name', 'Name');
	}
}
