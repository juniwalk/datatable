<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Enums;

enum Storage: string
{
	case Actions = 'actions';
	case Columns = 'columns';
	case Filters = 'filters';
}
