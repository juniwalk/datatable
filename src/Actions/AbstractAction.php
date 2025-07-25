<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Actions;

use JuniWalk\DataTable\Action;
use Nette\Application\UI\Control;

abstract class AbstractAction extends Control implements Action
{
	public function __construct(
		protected string $label,
	) {
	}


	public function getLabel(): string
	{
		return $this->label;
	}
}
