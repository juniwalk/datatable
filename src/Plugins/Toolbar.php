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
use JuniWalk\DataTable\Tools\FormatName;
use Nette\Application\UI\Template;

trait Toolbar
{
	/** @var array<string, Action> */
	protected array $toolbar = [];


	public function addToolbarLink(string $name, string $label, string $group = ''): LinkAction
	{
		return $this->addToolbarAction($name, new LinkAction($label, $group));
	}


	public function addToolbarButton(string $name, string $label, string $group = ''): ButtonAction
	{
		return $this->addToolbarAction($name, new ButtonAction($label, $group));
	}


	public function addToolbarDropdown(string $name, string $label, string $group = ''): DropdownAction
	{
		return $this->addToolbarAction($name, new DropdownAction($label, $group));
	}


	public function addToolbarCallback(string $name, string $label, string $group = ''): CallbackAction
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
		$this->addComponent($action, FormatName::component($name));
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


	/**
	 * @throws ActionNotFoundException
	 */
	public function removeToolbarAction(string $name): void
	{
		$action = $this->getToolbarAction($name);

		$this->removeComponent($action);
		unset($this->toolbar[$name]);
	}


	public function allowToolbarAction(string $name, Closure|bool $condition): static
	{
		$this->getToolbarAction($name)->setAllowCondition($condition);
		return $this;
	}


	protected function onRenderToolbar(Template $template): void
	{
		static $left = [
			'__filters' => null,
		];
		static $right = [
			'__settings' => null,
		];

		$toolbar = [];

		foreach ($this->toolbar as $name => $action) {
			if (!$action->isAllowed()) {
				continue;
			}

			$toolbar[$action->getGroup()][$name] = $action;
		}

		$template->toolbar = [
			// ? Render internal toolbar actions where desired (left or right)
			... array_intersect_key($toolbar, $left),		// ? Actions on left
			... array_diff_key($toolbar, $left, $right),	// ? Custom actions
			... array_intersect_key($toolbar, $right),		// ? Actions on right
		];
	}
}
