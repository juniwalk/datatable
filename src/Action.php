<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Nette\ComponentModel\IComponent;

interface Action extends IComponent
{
	public function getLabel(): ?string;
}
