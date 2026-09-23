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


	protected function setOption(Option|string $key, mixed $value = null): static
	{
		if ($key instanceof Option) {
			$key = $key->value;
		}

		$this->session->set($key, $value);
		return $this;
	}


	protected function getOption(Option|string $key, mixed $default = null): mixed
	{
		if ($key instanceof Option) {
			$key = $key->value;
		}

		return $this->session->get($key) ?? $default;
	}


	protected function validateSession(Presenter $presenter): void
	{
		$sessionName = Format::tokens('DataTable\{controlName}@{presenterName}', [
			'presenterName'	=> $presenter->getName(),
			'controlName'	=> $this->getUniqueId(),
		]);

		$this->rememberState ??= $presenter->getSession()->isStarted();
		$this->session = $presenter->getSession($sessionName);
	}
}
