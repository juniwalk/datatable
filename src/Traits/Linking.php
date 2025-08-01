<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Row;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Link;

/**
 * @phpstan-type LinkArgs array<string, mixed>
 */
trait Linking
{
	protected string $dest;

	/** @var LinkArgs */
	protected array $args = [];


	/**
	 * @param LinkArgs $args
	 */
	public function setLink(string $dest, array $args = []): self
	{
		$this->dest = $dest;
		$this->args = $args;
		return $this;
	}


	/**
	 * @param  LinkArgs $args
	 * @throws InvalidLinkException
	 */
	protected function createLink(Link|string $dest, array $args = []): Link|string
	{
		if ($dest instanceof Link ||
			str_starts_with($dest, '#') ||
			str_starts_with($dest, 'javascript:')) {
			// ! Will return string for javascript links in lazy mode
			return $dest;
		}

		$presenter = $this->getPresenter();

		if (str_contains($dest, ':')) {
			return $presenter->link($dest, $args);
		}

		$invalidLinkMode = $presenter->invalidLinkMode;
		$component = $this;

		do {
			$presenter->invalidLinkMode = $presenter::InvalidLinkException;

			if (!method_exists($component, 'link')) {
				continue;
			}

			try {
				return $component->link($dest, $args);

			} catch (InvalidLinkException) {
				continue;

			} finally {
				$presenter->invalidLinkMode = $invalidLinkMode;
			}

		} while ($component = $component->getParent());

		throw new InvalidLinkException;
	}


	/**
	 * @return LinkArgs
	 * @throws FieldNotFoundException
	 */
	protected function createArgs(Row $row): array
	{
		$args = $this->args;

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
