<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Plugins;

use JuniWalk\DataTable\Enums\Option;
use JuniWalk\Utils\Format;
use Nette\Application\UI\Presenter;
use Nette\Http\SessionSection;

trait Session
{
	protected ?bool $rememberState = null;

	protected SessionSection $session;


	/**
	 * @param array<string, mixed> $params
	 */
	public function loadState(array $params): void
	{
		if ($this->getOption(Option::IsFiltered, false)) {
			$params['filter'] ??= $this->getOption(Option::StateFilters, []);
		}

		if ($this->getOption(Option::IsLimited, false)) {
			$params['limit'] ??= $this->getOption(Option::StateLimit, null);
		}

		if ($this->getOption(Option::IsSorted, false)) {
			$params['sort'] ??= $this->getOption(Option::StateSorting, []);
		}

		parent::loadState($params);
	}


	public function setRememberState(?bool $rememberState = true): static
	{
		$this->rememberState = $rememberState;
		return $this;
	}


	public function isRememberState(): bool
	{
		return $this->rememberState ?? false;
	}


	public function clearRememberedState(): static
	{
		$this->session->remove();
		return $this;
	}


	protected function setOption(Option $key, mixed $value): static
	{
		$this->session->set($key->value, $value);
		return $this;
	}


	protected function getOption(Option $key, mixed $default = null): mixed
	{
		return $this->session->get($key->value) ?? $default;
	}


	protected function validateSession(Presenter $presenter): void
	{
		$sessionName = Format::tokens('DataTable\{controlName}@{presenterName}', [
			'presenterName'	=> $presenter->getName(),
			'controlName'	=> $this->getUniqueId(),
		]);

		if ($this->rememberState === null && $presenter->getSession()->isStarted()) {
			$this->rememberState = true;
		}

		$this->session = $presenter->getSession($sessionName);
	}
}
