<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Interfaces\SearchProvider;
use JuniWalk\DataTable\Filters\Interfaces\FilterSearchable;
use Nette\Forms\Controls\BaseControl;

/**
 * @phpstan-require-implements FilterSearchable
 */
trait SearchHandler
{
	use LinkHandler;

	protected ?SearchProvider $searchProvider = null;
	protected ?int $perPage = null;


	public function setSearchProvider(?SearchProvider $provider, ?int $perPage = null): static
	{
		$this->searchProvider = $provider;
		$this->perPage = $perPage;
		return $this;
	}


	public function getSearchProvider(): ?SearchProvider
	{
		return $this->searchProvider;
	}


	public function hasSearchProvider(): bool
	{
		return isset($this->searchProvider);
	}


	protected function applyAttributeSearch(BaseControl $input, string $fieldName): void
	{
		if ($this->searchProvider === null) {
			return;
		}

		$link = $this->createLink('search!', [
			'filterName' => $fieldName,
			'maxResults' => $this->perPage,
		]);

		$input->setHtmlAttribute('data-search', $link);
	}
}
