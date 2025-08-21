<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Plugins\Actions;
use JuniWalk\DataTable\Row;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;
use Nette\Utils\Random;

class DropdownAction extends ButtonAction
{
	use Actions;

	protected string $tag = 'button';


	public function addDivider(): static
	{
		$this->addAction(Random::generate(6), new DividerAction);
		return $this;
	}


	/**
	 * @throws InvalidStateException
	 */
	public function createButton(?Row $row): Html
	{
		$button = parent::createButton($row);
		$button->setAttribute('data-bs-toggle', 'dropdown');
		$button->addClass('dropdown-toggle');

		$dropdown = Html::el('div class="dropdown-menu dropdown-menu-end"');

		foreach ($this->actions as $action) {
			$item = $action->createButton($row);

			if (!$action instanceof DividerAction) {
				$item->addClass('dropdown-item');
			}

			if (!$action->isAllowed($row)) {
				$item->addClass('disabled');
			}

			$dropdown->addHtml($item);
		}

		return Html::el('div class="btn-group dropdown"')
			->addHtml($button)
			->addHtml($dropdown);
	}


	protected function validateParent(IContainer $parent): void
	{
		parent::validateParent($parent);

		$this->monitor($this::class, function() {
			if ($this->lookup($this::class, false)) {
				throw InvalidStateException::parentForbidden($this::class, $this);
			}
		});
	}
}
