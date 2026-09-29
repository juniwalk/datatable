<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Interfaces;

use JuniWalk\DataTable\Filters\Interfaces\FilterSearchable;
use JuniWalk\ORM\SearchPayload;
use Nette\Utils\Html;

interface SearchProvider
{
	/**
	 * @param  mixed|mixed[] $items
	 * @return Html[]
	 */
	public function findOptions(mixed $items): array;

	/**
	 * @return Html[]
	 */
	public function search(FilterSearchable $filter, string $query, SearchPayload $payload): ?array;
}
