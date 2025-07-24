<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Nette\Application\UI\Component;
use Nette\ComponentModel\IComponent;

/**
 * @template T of IComponent
 */
class Container extends Component
{
	public const string Actions = 'actions';
	public const string Columns = 'columns';
	public const string Filters = 'filters';


	/**
	 * @template TT of T
	 * @param  TT $component
	 * @return TT
	 */
	public function add(string $name, IComponent $component): IComponent
	{
		$this->addComponent($component, $name);
		return $component;
	}


	/**
	 * @return ($require is true ? T : ?T)
	 */
	public function get(string $name, bool $require = true): ?IComponent
	{
		return $this->getComponent($name, $require);
	}


	/**
	 * @return array<non-empty-string, T>
	 */
	public function list(): array
	{
		/** @var array<non-empty-string, T> */
		return iterator_to_array($this->getComponents());
	}


	/**
	 * @param T $component
	 */
	public function remove(IComponent|string $component): void
	{
		if (is_string($component)) {
			$component = $this->get($component);
		}

		$this->removeComponent($component);
	}
}
