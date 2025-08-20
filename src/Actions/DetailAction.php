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

/**
 * @inheritDoc
 */
class DetailAction extends AbstractAction implements CallbackRenderable, TemplateRenderable
{
	use RedirectAjaxHandler;
	use TableAncestor;
	use RendererCallback;
	use RendererTemplate;

	protected string $tag = 'a';


	/**
	 * @throws InvalidStateException
	 */
	public function handleOpen(int|string $id): void
	{
		$table = $this->getTable();
		$table->setActiveDetail($this);
		$table->redrawItem($id);

		$table->redrawControl('rows');
		$table->redrawControl('row-'.$id.'-detail');

		$this->redirect('this');
	}


	/**
	 * @throws InvalidStateException
	 */
	public function createButton(?Row $row): Html
	{
		if (is_null($row)) {
			throw InvalidStateException::rowRequired($this);
		}

		$table = $this->getTable();

		$button = parent::createButton($row);
		$button->setHref($this->link('open!', [
			'id' => $row->getId(),
		]));

		$snippetId = sprintf('row-%s-detail', $row->getId());
		$snippetId = $table->getSnippetId($snippetId);

		$button->setAttribute('data-dt-action', $this->getName());
		$button->setAttribute('data-dt-target', '#'.$snippetId);
		$button->addClass('ajax');

		return $button;
	}
}
