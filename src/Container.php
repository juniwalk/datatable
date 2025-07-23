<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Nette\Application\UI\Component;

class Container extends Component
{
	public const string Actions = 'actions';
	public const string Columns = 'columns';
	public const string Filters = 'filters';
}
