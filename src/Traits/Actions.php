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


	public function addActionLink(string $name, string $label): LinkAction
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
		return $this->__actions()->add($name, $action);
	}


	/**
	 * @return ($require is true ? Action : ?Action)
	 */
	public function getAction(string $name, bool $require = true): ?Action
	{
		return $this->__actions()->get($name, $require);
	}


	/**
	 * @return array<string, Action>
	 */
	public function getActions(): array
	{
		return $this->__actions()->list();
	}


	public function removeAction(string $name): void
	{
		$this->__actions()->remove($name);
	}


	/**
	 * @return Container<Action>
	 */
	private function __actions(): Container
	{
		return $this->getComponent(Container::Actions);
	}
}
