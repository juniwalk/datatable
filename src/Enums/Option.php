<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Enums;

enum Option: string
{
	case IsFiltered = 'isFiltered';
	case IsSorted = 'isSorted';
	case IsLimited = 'isLimited';
	case IsPinned = 'isPinned';

	case StateSorting = 'sortingState';
	case StateFilters = 'filtersState';
	case StateColumns = 'columnsState';
	case StateLimit = 'limitState';
}
