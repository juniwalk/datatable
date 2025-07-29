<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Exceptions\InvalidStateException;
use Nette\Application\Attributes\Persistent;
use Nette\Utils\Paginator;

trait Pagination
{
	#[Persistent]
	public int $page = 1;

	#[Persistent]
	public ?int $limit = null;
	private ?int $limitDefault = null;

	/** @var int[] */
	private array $limits = [10, 20, 50];


	public function handlePage(int $page): void
	{
		$this->page = max($page, 1);

		$this->redirect('this');
	}


	/**
	 * @throws InvalidStateException
	 */
	public function handleLimit(int $limit): void
	{
		if (!in_array($limit, $this->limits)) {
			throw InvalidStateException::limitUnknown($limit, $this->limits);
		}

		$this->limit = $limit;
		$this->page = 1;

		if ($this->isLimitDefault()) {
			$this->limit = null;
		}

		$this->redirect('this');
	}


	/**
	 * @param  int[] $limits
	 * @throws InvalidStateException
	 */
	public function setLimits(array $limits, bool $allowAll = false): self
	{
		$limits = array_filter($limits, fn($i) => $i > 0);
		$limits = array_unique(array_filter($limits));

		if (empty($limits)) {
			throw InvalidStateException::limitsEmpty();
		}

		$this->limits = $limits;

		if ($allowAll === true) {
			$this->limits[] = 0;
		}

		return $this;
	}


	public function getCurrentLimit(): int
	{
		return $this->limit ?? $this->limitDefault ?? $this->limits[0];
	}


	/**
	 * @throws InvalidStateException
	 */
	public function setDefaultLimit(?int $limit): self
	{
		if (!in_array($limit, $this->limits)) {
			throw InvalidStateException::limitUnknown($limit, $this->limits);
		}

		$this->limitDefault = $limit;
		return $this;
	}


	public function getLimitDefault(): ?int
	{
		return $this->limitDefault;
	}


	public function isLimitDefault(): bool
	{
		return $this->limit === $this->limitDefault;
	}


	public function renderPages(): void
	{
		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/../templates/table-pages.latte');

		if (!isset($this->source)) {
			throw new \Exception('No source set');
		}

		// todo: do not allow showing all rows with indetermined paginator
		if ($this->limit === 0) {
			return;
		}

		$pages = new Paginator;
		$pages->setPage($this->page);
		$pages->setItemsPerPage($this->getCurrentLimit());
		// todo: allow indetermined pagination when count is not known
		$pages->setItemCount($this->source->totalCount());

		$template->add('steps', $this->createSteps($pages));
		$template->add('pages', $pages);
		$template->add('page', $this->page);

		$template->render();
	}


	public function renderLimiter(): void
	{
		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/../templates/table-limiter.latte');

		// todo: add some numbers for the overview field
		$template->add('limit', $this->getCurrentLimit());
		$template->add('limits', $this->limits);

		$template->render();
	}


	/**
	 * @return array<int, int|null>
	 */
	protected function createSteps(Paginator $pages, int $steps = 7): array
	{
		$pageCount = $pages->getPageCount();
		$page = $pages->getPage();

		if ($pageCount <= 1) {
			return [];
		}

		if ($pageCount <= $steps) {
			return range(
				$pages->getFirstPage(),
				$pageCount,
			);
		}

		$slidingStart = (int) min(
			$pageCount - $steps + 2,
			$page - floor(($steps - 3) / 2),
		);

		if ($slidingStart < 2) $slidingStart = 2;

		$slidingEnd = (int) min(
			$slidingStart + $steps - 3,
			$pageCount - 1,
		);

		$pages = [1];

		if ($slidingStart > 2) {
			$pages[] = null;
		}

		$pages = array_merge($pages, range($slidingStart, $slidingEnd));

		if ($slidingEnd < $pageCount - 1) {
			$pages[] = null;
		}

		$pages[] = $pageCount;

		return $pages;
	}
}
