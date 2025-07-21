<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\ButtonAction;

trait Actions
{
	// todo: store ctions in subcomponent so there is no name clashing with actions / filters


	public function addActionButton(string $name, ?string $label = null): ButtonAction
	{
		$this->addComponent($column = new ButtonAction($label), $name);
		return $column;
	}


	/**
	 * @return ($require is true ? Action : ?Action)
	 */
	public function getAction(string $name, bool $require = true): ?Action
	{
		return $this->getComponent($name, $require);
	}


	/**
	 * @return iterable<string, Action>
	 */
	public function getActions(): iterable
	{
		return $this->getComponents(null, Action::class);	// @phpstan-ignore return.type (List is filtered for Action::class)
	}


	public function removeAction(string $name): void
	{
		// todo: make sure this works properly as there was PHPStan issue
		$this->removeComponent($this->getAction($name));
	}
}
