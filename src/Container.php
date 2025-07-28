<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\SignalReceiver;
use Nette\Application\UI\StatePersistent;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;

// todo: implement name checking

/**
 * @template T of IComponent
 */
class Container extends Component implements IContainer, SignalReceiver, StatePersistent
{
	// todo: move this into Enum
	public const string Actions = 'actions';
	public const string Columns = 'columns';
	public const string Filters = 'filters';

	/** @var array<string, T> */
	private array $components = [];


	/**
	 * @param  T $component
	 * @return T
	 */
	public function add(string $name, IComponent $component): IComponent
	{
		if (isset($this->components[$name])) {
			// todo: throw
			throw new \Exception;
		}

		$component->setParent($this, $name);

		$this->components[$name] = $component;
		return $component;
	}


	/**
	 * @return ($require is true ? T : ?T)
	 */
	public function get(string $name, bool $require = true): ?IComponent
	{
		if ($require && !isset($this->components[$name])) {
			// todo: throw
			throw new \Exception;
		}

		return $this->components[$name] ?? null;
	}


	/**
	 * @return array<string, T>
	 */
	public function list(): array
	{
		return $this->components;
	}


	/**
	 * @param T $component
	 */
	public function remove(IComponent|string $component): void
	{
		if (is_string($component)) {
			$component = $this->get($component);
		}

		$name = $component->getName();

		if (!$name || $component !== $this->get($name, false)) {
			// todo: throw
			throw new \Exception;
		}

		unset($this->components[$name]);
		$component->setParent(null);
	}


	public function getUniqueId(): string
	{
		return $this->lookupPath(Presenter::class);
	}


	public function signalReceived(string $signal): void {}


	/**
	 * @param array<string, mixed> $params
	 */
	public function loadState(array $params): void {}


	/**
	 * @param array<string, mixed> $params
	 * @param-out array<string, mixed> $params
	 */
	public function saveState(array &$params): void {}


	/**
	 * @param T $component
	 */
	public function addComponent(IComponent $component, ?string $name): self
	{
		if (!$name ??= $component->getName()) {
			// todo: throw
			throw new \Exception;
		}

		$this->add($name, $component);
		return $this;
	}


	/**
	 * @return ($require is true ? T : ?T)
	 */
	public function getComponent(string $name, bool $require = true): ?IComponent
	{
		return $this->get($name, $require);
	}


	/**
	 * @return array<string, T>
	 */
	public function getComponents(): array
	{
		return $this->list();
	}


	/**
	 * @param T $name
	 */
	public function removeComponent(IComponent|string $name): void
	{
		$this->remove($name);
	}
}
