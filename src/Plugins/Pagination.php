<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Enums\Option;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use Nette\Application\Attributes\Persistent;
use Nette\Utils\Paginator;

trait Pagination
{
	#[Persistent]
	public int $page = 1 {
		set => max($value, 1);
	}

	#[Persistent]
	public ?int $limit = null {
		set(?int $limit) {
			if ($limit && !in_array($limit, $this->limits)) {
				$limit = null;
			}

			$this->limit = $limit;
		}
	}

	/** @var int[] */
	protected array $limits = [10, 20, 50];
	protected ?int $limitDefault = null;


	public function handlePage(int $page): void
	{
		$this->page = $page;

		$this->redrawControl('paginator');
		$this->redrawControl('table');
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

		if ($this->rememberState) {
			$this->setOption(Option::IsLimited, !empty($this->limit));
			$this->setOption(Option::StateLimit, $this->limit);
		}

		$this->redrawControl();
		$this->redirect('this');
	}


	public function setPage(int $page): static
	{
		$this->page = $page;
		return $this;
	}


	public function getPage(): int
	{
		return $this->page;
	}


	public function getOffset(): int
	{
		$limit = $this->getCurrentLimit();
		return $limit * ($this->page - 1);
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


	/**
	 * @return int[]
	 */
	public function getLimits(): array
	{
		return $this->limits;
	}


	public function getCurrentLimit(): int
	{
		if ($this->limit && $this->getOption(Option::IsLimited)) {
			return $this->limit;
		}

		return $this->limitDefault ?? $this->limits[0];
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

		$source = $this->getSource();

		// todo: do not allow showing all rows with indetermined paginator
		if ($this->limit === 0) {
			return;
		}

		$pages = new Paginator;
		$pages->setPage($this->page);
		$pages->setItemsPerPage($this->getCurrentLimit());
		// todo: allow indetermined pagination when count is not known
		$pages->setItemCount($source->getCount());

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

		$source = $this->getSource();

		$limit = $this->getCurrentLimit();
		$count = $source->getCount();

		$offsetStart = $this->getOffset();
		$offsetEnd = $offsetStart + $limit;

		if ($offsetEnd > $count) {
			$offsetEnd = null;
		}

		$template->add('limits', $this->limits);
		$template->add('limit', $limit);
		$template->add('offsetStart', $offsetStart + 1);
		$template->add('offsetEnd', $offsetEnd ?: $count);
		$template->add('count', $count);

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
