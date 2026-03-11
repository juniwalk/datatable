<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use JuniWalk\DataTable\Row;
use JuniWalk\Utils\Interfaces\EventHandler;
use JuniWalk\Utils\Traits\Events;
use Nette\Utils\Html;

class CallbackAction extends AbstractAction implements EventHandler
{
	use Events;

	protected string $tag = 'a';


	public function __construct(
		protected string $label,
		protected string $group = '',
	) {
		$this->watchAny('render,click');
	}


	public function handleAction(mixed $id): void
	{
		$this->trigger('click', $id);
		$this->redirect('this');
	}


	public function addClickCallback(callable $callback): static
	{
		$this->when('click', $callback);
		return $this;
	}


	public function createButton(?Row $row): Html
	{
		$button = parent::createButton($row);

		$this->trigger('render', $button, $row);

		$button->setHref($this->link('action!', [
			'id' => $row?->getId(),
		]));

		return $button;
	}
}
