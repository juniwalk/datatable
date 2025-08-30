<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use Closure;
use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Actions\ButtonAction;
use JuniWalk\DataTable\Actions\CallbackAction;
use JuniWalk\DataTable\Actions\DropdownAction;
use JuniWalk\DataTable\Actions\LinkAction;
use JuniWalk\DataTable\Exceptions\ActionNotFoundException;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;

trait Toolbar
{
	/** @var array<string, Action> */
	protected array $toolbar = [];


	public function addToolbarLink(string $name, string $label, ?string $group = null): LinkAction
	{
		return $this->addToolbarAction($name, new LinkAction($label, $group));
	}


	public function addToolbarButton(string $name, string $label, ?string $group = null): ButtonAction
	{
		return $this->addToolbarAction($name, new ButtonAction($label, $group));
	}


	public function addToolbarDropdown(string $name, string $label, ?string $group = null): DropdownAction
	{
		return $this->addToolbarAction($name, new DropdownAction($label, $group));
	}


	public function addToolbarCallback(string $name, string $label, ?string $group = null): CallbackAction
	{
		return $this->addToolbarAction($name, new CallbackAction($label, $group));
	}


	/**
	 * @template T of Action
	 * @param  T $action
	 * @return T
	 */
	public function addToolbarAction(string $name, Action $action): Action
	{
		$this->addComponent($action, $name);
		$this->toolbar[$name] = $action;

		return $action;
	}


	/**
	 * @return ($require is true ? Action : ?Action)
	 * @throws ActionNotFoundException
	 */
	public function getToolbarAction(string $name, bool $require = true): ?Action
	{
		if ($require && !isset($this->toolbar[$name])) {
			throw ActionNotFoundException::fromName($name);
		}

		return $this->toolbar[$name] ?? null;
	}


	/**
	 * @return array<string, Action>
	 */
	public function getToolbarActions(): array
	{
		return $this->toolbar;
	}


	public function removeToolbarAction(string $name): void
	{
		if (!$action = $this->getToolbarAction($name)) {
			return;
		}

		$this->removeComponent($action);
		unset($this->actions[$name]);
	}


	public function allowToolbarAction(string $name, Closure|bool $condition): static
	{
		$this->getToolbarAction($name)->setAllowCondition($condition);
		return $this;
	}


	protected function onRenderToolbar(DefaultTemplate $template): void
	{
		static $internal = [
			'__filters' => null,
			'__columns' => null,
		];

		$toolbar = [];

		foreach ($this->toolbar as $name => $action) {
			if (!$action->isAllowed()) {
				continue;
			}

			$toolbar[$action->getGroup()][$name] = $action;
		}

		$template->add('toolbar', [
			// ? Render internal toolbar actions first (on the left)
			... array_intersect_key($toolbar, $internal),
			... array_diff_key($toolbar, $internal),
		]);
	}
}
