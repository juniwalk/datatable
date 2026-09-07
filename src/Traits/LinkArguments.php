<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Row;

use function is_null;
use function is_string;
use function str_starts_with;
use function substr;

/**
 * @phpstan-import-type LinkArgs from LinkHandler
 */
trait LinkArguments
{
	protected string $dest;

	/** @var LinkArgs */
	protected array $args = [];


	/**
	 * @param LinkArgs $args
	 */
	public function setLink(string $dest, array $args = []): static
	{
		$this->dest = $dest;
		$this->args = $args;
		return $this;
	}


	/**
	 * @return LinkArgs
	 * @throws FieldNotFoundException
	 */
	protected function createArgs(?Row $row): array
	{
		$args = $this->args;

		if (is_null($row)) {
			return $args;
		}

		foreach ($args as $key => $arg) {
			if (!is_string($arg) || !str_starts_with($arg, '@')) {
				continue;
			}

			$args[$key] = $row->getValue(substr($arg, 1));
		}

		$args[$row->getPrimaryKey()] ??= $row->getId();
		return $args;
	}
}
