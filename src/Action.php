<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Closure;
use JuniWalk\DataTable\Interfaces\Attributable;
use JuniWalk\DataTable\Row;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;

interface Action extends IComponent, Attributable
{
	public function setLabel(string $label): static;
	public function getLabel(): string;

	public function setGroup(?string $group): static;
	public function getGroup(): ?string;

	public function setAllowCondition(Closure|bool $condition): static;
	public function isAllowed(?Row $row = null): bool;

	public function createButton(?Row $row): Html;
	public function render(?Row $row = null): void;
}
