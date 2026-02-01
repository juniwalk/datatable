<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Interfaces\CallbackRenderable;
use JuniWalk\DataTable\Interfaces\TemplateRenderable;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Traits\RendererCallback;
use JuniWalk\DataTable\Traits\RendererTemplate;
use JuniWalk\DataTable\Traits\TableAncestor;
use JuniWalk\Utils\Traits\RedirectAjaxHandler;
use Nette\Utils\Html;

class DetailAction extends AbstractAction implements CallbackRenderable, TemplateRenderable
{
	use RedirectAjaxHandler;
	use RendererCallback;
	use RendererTemplate;
	use TableAncestor;

	protected string $tag = 'a';


	/**
	 * @throws InvalidStateException
	 */
	public function handleOpen(int|string $id): void
	{
		$table = $this->getTable();
		$table->setActiveDetail($this);
		$table->setItemRedraw($id, true);

		$this->redirect('this');
	}


	/**
	 * @throws InvalidStateException
	 */
	public function createButton(?Row $row): Html
	{
		$this->targetNewTab = false;

		if (is_null($row)) {
			throw InvalidStateException::rowRequired($this);
		}

		$snippetId = sprintf('row-%s-detail', $row->getId());
		$snippetId = $this->getTable()->getSnippetId($snippetId);

		$button = parent::createButton($row)->addClass('ajax');
		$button->setAttribute('data-dt-action', $this->getName());
		$button->setAttribute('data-dt-target', '#'.$snippetId);
		$button->setHref($this->link('open!', [
			'id' => $row->getId(),
		]));

		return $button;
	}
}
