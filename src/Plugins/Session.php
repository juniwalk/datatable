<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Enums\Option;
use JuniWalk\Utils\Format;
use Nette\Http\SessionSection;

trait Session
{
	protected SessionSection $session;

	// protected bool $rememberFilters = true;
	// protected bool $rememberSorting = true;


	public function getSessionName(): string
	{
		if (!$presenter = $this->getPresenterIfExists()) {
			// todo: throw ComponentNotAnchoredException
			throw new \Exception;
		}

		return Format::tokens('DataTable\{controlName}@{presenterName}', [
			'presenterName'	=> $presenter->getName(),
			'controlName'	=> $this->getUniqueId(),
		]);
	}


	protected function setOption(Option $key, mixed $value): self
	{
		$this->session->set($key->value, $value);
		return $this;
	}


	protected function getOption(Option $key, mixed $default = null): mixed
	{
		return $this->session->get($key->value) ?? $default;
	}


	protected function validateSession(): void
	{
		if (!$presenter = $this->getPresenterIfExists()) {
			// todo: throw ComponentNotAnchoredException
			throw new \Exception;
		}

		$this->session = $presenter->getSession($this->getSessionName());
	}
}
