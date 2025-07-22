<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use JuniWalk\DataTable\Row;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;

interface Action extends IComponent
{
	public function getLabel(): ?string;

	public function render(Row $row): Html;
}
