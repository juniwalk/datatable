<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Enums;

enum Option: string
{
	case HiddenColumns = 'hiddenColumns';
	case IsFiltered = 'isFiltered';
}
