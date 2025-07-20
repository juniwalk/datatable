<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use Nette\Application\Attributes\Persistent;

trait Filters
{
	#[Persistent]
	/** @var array<string, mixed> */
	public array $filter = [];


	// todo: add filter

	// todo: get filter

	// todo: remove filter


	public function handleClear(string $column): void
	{
		unset($this->filter[$column]);

		$this->redirect('this');
	}


	public function handleClearAll(): void
	{
		$this->filter = [];

		$this->redirect('this');
	}
}
