<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\Form\SearchPayload;
use JuniWalk\DataTable\Interfaces\CallbackSearchable;
use Closure;

/**
 * @phpstan-require-implements CallbackSearchable
 */
trait SearchCallback
{
	use TableAncestor;

	protected ?Closure $search = null;


	public function setSearch(?Closure $search, ?int $maxResults = null): static
	{
		$this->search = $search;

		if ($this->search !== null) {
			$searchSignal = $this->getTable()->link('search!', $this->getName(), $maxResults);
			$this->setAttribute('data-search', $searchSignal);
		}

		return $this;
	}


	public function getSearch(): ?Closure
	{
		return $this->search;
	}


	public function hasSearch(): bool
	{
		return isset($this->search);
	}


	/**
	 * @return array<string, mixed[]>
	 */
	public function searchCallback(string $query, SearchPayload $payload): ?array
	{
		if ($this->search === null) {
			return null;
		}

		return ($this->search)($query, $payload);
	}
}
