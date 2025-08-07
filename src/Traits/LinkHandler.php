<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Link;

/**
 * @phpstan-type LinkArgs array<string, mixed>
 */
trait LinkHandler
{
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
}
