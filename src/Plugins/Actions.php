<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use Closure;
use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\CallbackAction;
use JuniWalk\DataTable\Actions\LinkAction;

trait Actions
{
	/** @var array<string, Action> */
	protected array $actions = [];


	public function addActionLink(string $name, string $label): LinkAction
	{
		// todo: allow $name to be signal (clear unwanted characters for $name)
		return $this->addAction($name, new LinkAction($label));
	}


	// todo: new action addActionDropdown ?


	public function addActionCallback(string $name, string $label): CallbackAction
	{
		return $this->addAction($name, new CallbackAction($label));
	}


	/**
	 * @template T of Action
	 * @param  T $action
	 * @return T
	 */
	public function addAction(string $name, Action $action): Action
	{
		$this->addComponent($action, $name);
		$this->actions[$name] = $action;

		return $action;
	}


	/**
	 * @return ($require is true ? Action : ?Action)
	 */
	public function getAction(string $name, bool $require = true): ?Action
	{
		if ($require && !isset($this->actions[$name])) {
			throw new \Exception;
		}

		return $this->actions[$name] ?? null;
	}


	/**
	 * @return array<string, Action>
	 */
	public function getActions(): array
	{
		return $this->actions;
	}


	public function removeAction(string $name): void
	{
		if (!$action = $this->getAction($name)) {
			return;
		}

		$this->removeComponent($action);
		unset($this->actions[$name]);
	}


	public function allowRowAction(string $name, Closure|bool $condition): self
	{
		$this->getAction($name)->setAllowCondition($condition);
		return $this;
	}
}
