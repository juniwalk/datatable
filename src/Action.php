<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Closure;
use JuniWalk\DataTable\Row;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;

interface Action extends IComponent
{
	public function setLabel(string $label): self;
	public function getLabel(): string;

	public function setRowAllowed(Closure $condition): self;
	public function isRowAllowed(Row $row): bool;

	public function render(Row $row): Html;
}
