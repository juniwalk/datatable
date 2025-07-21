<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\LinkAction;

trait Actions
{
	// todo: store actions in subcomponent so there is no name clashing with actions / filters

	// todo: new action addActionCallback ?
	// todo: new action addActionDropdown ?


	public function addAction(string $name, ?string $label = null): LinkAction
	{
		$this->addComponent($column = new LinkAction($label), $name);
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
