<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use JuniWalk\DataTable\Row;
use Nette\Utils\Html;

final class DividerAction extends AbstractAction
{
	protected string $tag = 'div';

	public function __construct(
		protected string $label = '',
		protected string $group = '',
	) {
	}


	public function createButton(?Row $row): Html
	{
		$parent = $this->getParent() ?? $this;

		return Html::el($this->tag, [
			'class' => match ($parent::class) {
				DropdownAction::class => 'dropdown-divider',
				default => 'vr h-100 mx-lg-2',
			},
		]);
	}
}
