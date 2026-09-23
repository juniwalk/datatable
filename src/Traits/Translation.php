<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use Nette\Localization\Translator;
use Stringable;

use function is_string;
use function preg_match;
use function str_contains;

trait Translation
{
	protected ?Translator $translator = null;
	protected bool $translateDisabled = false;


	public function setTranslator(?Translator $translator): static
	{
		$this->translator = $translator;
		return $this;
	}


	public function getTranslator(): ?Translator
	{
		return $this->translator;
	}


	public function setTranslateDisabled(bool $translateDisabled = true): static
	{
		$this->translateDisabled = $translateDisabled;
		return $this;
	}


	public function isTranslateDisabled(): bool
	{
		return $this->translateDisabled;
	}


	protected function translate(Stringable|string|null $message, mixed ...$params): Stringable|string
	{
		if ($this->translateDisabled || !$message || !isset($this->translator)) {
			return $message ?? '';
		}

		if (is_string($message) && (!str_contains($message, '.') || !preg_match('/^(([a-z0-9\_\-]+)\.)+(?2)$/i', $message))) {
			return $message;
		}

		return $this->translator->translate($message, $params);
	}
}
