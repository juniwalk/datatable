<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Action;
use Nette\Utils\Html;

class ActionColumn extends AbstractColumn
{
	protected string $align = 'end';

	/** @var array<string, Action> */
	protected array $actions;


	/**
	 * @param iterable<string, Action> $actions
	 */
	public function addActions(iterable $actions): self
	{
		if (!is_array($actions)) {
			$actions = iterator_to_array($actions);
		}

		$this->actions = $actions;
		return $this;
	}


	public function render(mixed $row): void
	{
		$toolbar = Html::el('div class="btn-toolbar gap-1 justify-content-end"');

		// todo: build list of buttons with actions
		foreach ($this->actions as $name => $action) {
			$button = Html::el('a class="btn btn-xs btn-secondary"', $action->getLabel());

			$toolbar->addHtml($button);
		}

		echo $toolbar;
	}
}
