<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use Nette\Application\Attributes\Persistent;

trait Pagination
{
	#[Persistent]
	public int $page = 1;

	#[Persistent]
	public ?int $limit = null;
	private ?int $limitDefault = null;

	/** @var int[] */
	private array $perPage = [10, 20, 40, 60, 80];


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
		if (!in_array($limit, $this->perPage)) {
			// todo: throw LimitInvalidException
			throw new \Exception;
		}

		if ($limit === $this->limitDefault) {
			$limit = null;
		}

		$this->limit = $limit;

		$this->redirect('this');
	}


	/**
	 * @param int[] $perPage
	 */
	public function setPerPage(array $perPage): self
	{
		$perPage = array_unique(array_filter($perPage));

		if (empty($perPage)) {
			// todo: throw PerPageListException
			throw new \Exception;
		}

		$this->perPage = $perPage;
		return $this;
	}


	public function setDefaultLimit(?int $limit): self
	{
		if (!in_array($limit, $this->perPage)) {
			// todo: throw LimitInvalidException
			throw new \Exception;
		}

		$this->limitDefault = $limit;
		return $this;
	}
}
