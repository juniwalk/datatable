<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

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
	private array $limits = [10, 20, 40, 60, 80];

	private Paginator $paginator;


	// todo: allow showing all the rows somehow
	// todo: allow indetermined pagination when count is not known


	public function handlePage(int $page): void
	{
		// todo: check page validity

		$this->page = $page;

		$this->redirect('this');
	}


	public function handleLimit(int $limit): void
	{
		if (!in_array($limit, $this->limits)) {
			// todo: throw LimitInvalidException
			throw new \Exception;
		}

		$this->limit = $limit;

		if ($this->isLimitDefault()) {
			$this->limit = null;
		}

		$this->redirect('this');
	}


	/**
	 * @param int[] $limits
	 */
	public function setLimits(array $limits): self
	{
		$limits = array_unique(array_filter($limits));

		if (empty($limits)) {
			// todo: throw PerPageListException
			throw new \Exception;
		}

		$this->limits = $limits;
		return $this;
	}


	public function getCurrentLimit(): int
	{
		return $this->limit ?? $this->limitDefault ?? $this->limits[0];
	}


	public function setDefaultLimit(?int $limit): self
	{
		if (!in_array($limit, $this->limits)) {
			// todo: throw LimitInvalidException
			throw new \Exception;
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

		$template->add('page', $this->page);

		$template->render();
	}


	public function renderLimiter(): void
	{
		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->createTemplate();
		$template->setFile(__DIR__.'/../templates/table-limiter.latte');

		$template->add('limit', $this->getCurrentLimit());
		$template->add('limits', $this->limits);

		$template->render();
	}
}
