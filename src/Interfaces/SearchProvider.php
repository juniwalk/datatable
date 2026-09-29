<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Interfaces;

use JuniWalk\DataTable\Filters\Interfaces\FilterSearchable;
use JuniWalk\Form\SearchPayload;
use JuniWalk\ORM\Entity\Interfaces\HtmlOption;

interface SearchProvider
{
	/**
	 * @param  mixed|mixed[] $items
	 * @return HtmlOption[]
	 */
	public function findOptions(mixed $items): array;

	/**
	 * @return HtmlOption[]
	 */
	public function search(FilterSearchable $filter, string $query, SearchPayload $payload): ?array;
}
