<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Row;
use Nette\Utils\Html;

class ActionColumn extends AbstractColumn
{
	protected string $align = 'end';

	/** @var array<string, Action> */
	protected array $actions;


	/**
	 * @param array<string, Action> $actions
	 */
	public function addActions(array $actions): self
	{
		$this->actions = $actions;
		return $this;
	}


	public function render(Row $row): void
	{
		$toolbar = Html::el('div class="btn-toolbar gap-1 justify-content-end"');

		foreach ($this->actions as $name => $action) {
			// todo: check if the row is allowed to have this action

			$button = $action->render($row);
			$toolbar->addHtml($button);
		}

		echo $toolbar;
	}
}
