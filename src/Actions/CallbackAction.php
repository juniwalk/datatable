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
		protected ?string $group = null,
	) {
		$this->watch('click');
	}


	public function handleAction(mixed $id): void
	{
		$this->trigger('click', $id);
		$this->redirect('this');
	}


	public function addClickCallback(callable $callback): self
	{
		$this->when('click', $callback);
		return $this;
	}


	public function render(?Row $row = null, bool $return = false): ?Html
	{
		$link = $this->link('action!', [
			'id' => $row?->getId(),
		]);

		$button = parent::render($row, true);
		$button->setHref($link);

		if ($return === true) {
			return $button;
		}

		echo $button; return null;
	}
}
