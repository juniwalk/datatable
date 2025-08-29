<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Exceptions\SourceMissingException;
use JuniWalk\DataTable\Traits\Translation;
use JuniWalk\Utils\Interfaces\EventHandler;
use JuniWalk\Utils\Traits\Events;
use JuniWalk\Utils\Traits\RedirectAjaxHandler;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use stdClass;
use Stringable;

class Table extends Control implements EventHandler
{
	use Events, Translation, RedirectAjaxHandler;

	use Plugins\Session;
	use Plugins\Sources;
	use Plugins\Columns;
	use Plugins\Filters;
	use Plugins\Actions;
	use Plugins\Toolbar;
	use Plugins\Sorting;
	use Plugins\Pagination;

	protected Stringable|string|null $caption = null;


	public function setCaption(Stringable|string|null $caption): static
	{
		$this->caption = $caption;
		return $this;
	}


	public function getCaption(): Stringable|string|null
	{
		return $this->caption;
	}


	public function flashMessage(Stringable|stdClass|string $message, string $type = 'info'): stdClass
	{
		return $this->getPresenter()->flashMessage($message, $type);
	}


	/**
	 * @throws SourceMissingException
	 */
	public function render(): void
	{
		$source = $this->getSource();

		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/table.latte');

		if ($actions = $this->getActions()) {
			$this->addColumnAction('__actions', 'datatable.column.action', $actions);
		}

		$this->trigger('render', $template);

		$items = isset($this->redrawItem)
			? $source->fetchItem($this->redrawItem)
			: $source->fetchItems($this);

		$rows = [];

		$this->trigger('load', $items, $source);

		foreach ($items as $item) {
			$rows[] = $row = new Row($item, $source);
			$this->trigger('item', $item, $row);
		}

		$template->add('autoSubmit', $this->getAutoSubmit());
		$template->add('toolbar', $this->getToolbarActionsGrouped());
		$template->add('columns', $this->getColumns());
		$template->add('filters', $this->getFilters());
		$template->add('table', $this);
		$template->add('rows', $rows);
		$template->render();
	}


	protected function validateParent(IContainer $parent): void
	{
		$this->monitor(Presenter::class, function(Presenter $presenter) {
			$this->validateSession($presenter);
			$this->validateSources($presenter);
		});

		parent::validateParent($parent);

		$this->watchAny('render,load,item');
		$this->when('render', function() {
			$this->onRenderFilters();
			$this->onRenderSorting();
			$this->onRenderColumns();
		});
	}
}
