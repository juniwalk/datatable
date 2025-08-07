<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use Closure;
use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\ButtonAction;
use JuniWalk\DataTable\Actions\LinkAction;
use JuniWalk\DataTable\Container;
use JuniWalk\DataTable\Enums\Storage;

trait Toolbar
{
	// todo: new action addToolbarCallback ?
	// todo: new action addToolbarDropdown ?


	public function addToolbarButton(string $name, string $label, ?string $group = null): ButtonAction
	{
		return $this->addToolbarAction($name, new ButtonAction($label, $group));
	}


	public function addToolbarLink(string $name, string $label, ?string $group = null): LinkAction
	{
		// todo: allow $name to be signal (clear unwanted characters for $name)

		return $this->addToolbarAction($name, new LinkAction($label, $group));
	}


	/**
	 * @template T of Action
	 * @param  T $action
	 * @return T
	 */
	public function addToolbarAction(string $name, Action $action): Action
	{
		/** @var T */
		return $this->__toolbar()->add($name, $action);
	}


	/**
	 * @return ($require is true ? Action : ?Action)
	 */
	public function getToolbarAction(string $name, bool $require = true): ?Action
	{
		return $this->__toolbar()->get($name, $require);
	}


	/**
	 * @return array<string, Action>
	 */
	public function getToolbarActions(): array
	{
		return $this->__toolbar()->list();
	}


	/**
	 * @return array<?string, array<string, Action>>
	 */
	public function getToolbarActionsGrouped(): array
	{
		static $internal = [
			'__filters' => null,
			'__columns' => null,
		];

		$actions = $this->getToolbarActions();
		$toolbar = [];

		foreach ($actions as $name => $action) {
			if (!$action->isAllowed()) {
				continue;
			}

			$toolbar[$action->getGroup()][$name] = $action;
		}

		return [
			... array_intersect_key($toolbar, $internal),
			... array_diff_key($toolbar, $internal),
		];
	}


	public function removeToolbarAction(string $name): void
	{
		$this->__toolbar()->remove($name);
	}


	public function allowToolbarAction(string $name, Closure|bool $condition): self
	{
		$this->getToolbarAction($name)->setAllowCondition($condition);
		return $this;
	}


	/**
	 * @return Container<Action>
	 */
	private function __toolbar(): Container
	{
		return $this->getComponent(Storage::Toolbar->value);
	}
}
