<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Sources;

use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Source;
use JuniWalk\Utils\Traits\Events;

/**
 * @phpstan-import-type Items from Source
 */
abstract class AbstractSource implements Source
{
	use Events;


	public function __construct()
	{
		$this->watch('load');
		$this->watch('item');
	}


	/**
	 * @return Row[]
	 */
	public function fetchRows(): iterable
	{
		$items = $this->fetchItems();
		$rows =  [];

		$this->trigger('load', $items);

		foreach ($items as $item) {
			$rows[] = $row = new Row($item, $this);
			$this->trigger('item', $item, $row);
		}

		return $rows;
	}


	/**
	 * @return Items
	 */
	abstract protected function fetchItems(): iterable;
}
