<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Exceptions\SourceMissingException;
use JuniWalk\DataTable\Traits\Translation;
use JuniWalk\Utils\Interfaces\EventAutoWatch;
use JuniWalk\Utils\Interfaces\EventHandler;
use JuniWalk\Utils\Traits\Events;
use JuniWalk\Utils\Traits\RedirectAjaxHandler;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;
use Nette\ComponentModel\IContainer;
use stdClass;
use Stringable;

class Table extends Control implements EventHandler, EventAutoWatch
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
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/templates/table.latte');

		$this->trigger('render', $template);

		$template->rows = $this->getRows();
		$template->table = $this;
		$template->render();
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function validateParent(IContainer $parent): void
	{
		$this->monitor(Presenter::class, function(Presenter $presenter) {
			$this->validateSession($presenter);
			$this->validateSources($presenter);
		});

		parent::validateParent($parent);

		$this->watchAny('render,load,item');
		$this->when('render', function(Template $template) {
			$this->onRenderFilters($template);
			$this->onRenderSorting($template);
			$this->onRenderColumns($template);
			$this->onRenderToolbar($template);
		});
	}
}
