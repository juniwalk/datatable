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
	public function __construct(
		protected string $label = '',
		protected ?string $group = null,
	) {
	}


	public function createButton(?Row $row): Html
	{
		return Html::el('div class="dropdown-divider"');
	}
}
