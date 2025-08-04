<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Action;
use JuniWalk\DataTable\Enums\Align;
use JuniWalk\DataTable\Row;
use Nette\Utils\Html;

class ActionColumn extends AbstractColumn
{
	protected Align $align = Align::Right;

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
		echo $this->renderValue($row);
	}


	protected function renderValue(Row $row): Html
	{
		$toolbar = Html::el('div class="btn-toolbar flex-nowrap gap-1 justify-content-end"');

		foreach ($this->actions as $name => $action) {
			// todo: check if the row is allowed to have this action
			// if (!$row->isActionAllowed($name, $action)) {
			// 	continue;
			// }

			$button = $action->render($row);
			$toolbar->addHtml($button);
		}

		return $toolbar;
	}
}
