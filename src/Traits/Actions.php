<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\LinkAction;
use JuniWalk\DataTable\Container;

trait Actions
{
	// todo: new action addActionCallback ?
	// todo: new action addActionDropdown ?


	public function addActionLink(string $name, ?string $label = null): LinkAction
	{
		// todo: allow $name to be signal (clear unwanted characters for $name)
		return $this->addAction($name, new LinkAction($label));
	}


	/**
	 * @template T of Action
	 * @param  T $action
	 * @return T
	 */
	public function addAction(string $name, Action $action): Action
	{
		$this['actions']->addComponent($action, $name);
		return $action;
	}


	/**
	 * @return ($require is true ? Action : ?Action)
	 */
	public function getAction(string $name, bool $require = true): ?Action
	{
		return $this['actions']->getComponent($name, $require);
	}


	/**
	 * @return array<string, Action>
	 */
	public function getActions(): array
	{
		$actions = $this['actions']->getComponents(null, Action::class);

		/** @var array<string, Action> */
		return iterator_to_array($actions);
	}


	public function removeAction(string $name): void
	{
		// todo: make sure this works properly as there was PHPStan issue
		$this['actions']->removeComponent($this->getAction($name));
	}


	protected function createComponentActions(): Container
	{
		return new Container;
	}
}
