<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use Nette\Localization\Translator;
use Stringable;

trait Translation
{
	protected ?Translator $translator = null;


	public function setTranslator(?Translator $translator): static
	{
		$this->translator = $translator;
		return $this;
	}


	public function getTranslator(): ?Translator
	{
		return $this->translator;
	}


	protected function translate(Stringable|string $message, string ...$params): Stringable|string
	{
		if (!isset($this->translator)) {
			return $message;
		}

		if (is_string($message) && !preg_match('/^(([a-z0-9\_]+)\.)+(?2)$/i', $message)) {
			return $message;
		}

		return $this->translator->translate($message, $params);
	}
}
