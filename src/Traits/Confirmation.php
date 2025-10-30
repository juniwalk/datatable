<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use Closure;
use JuniWalk\DataTable\Row;
use Nette\Utils\Strings;

trait Confirmation
{
	use Translation;

	public const string ConfirmAttribute = 'data-dt-confirm';

	protected ?string $confirmMessage = null;
	protected ?Closure $confirmCallback = null;


	public function setConfirmMessage(?string $message, ?Closure $callback = null): static
	{
		$this->confirmCallback = $callback;
		$this->confirmMessage = $message;
		return $this;
	}


	public function getConfirmMessage(): ?string
	{
		return $this->confirmMessage;
	}


	public function createConfirm(?Row $row): ?string
	{
		if (!$this->confirmMessage && !$this->confirmCallback) {
			return null;
		}

		$message = $this->translate($this->confirmMessage);
		$message = call_user_func(
			$this->confirmCallback ?? fn($x) => $x,
			Strings::replace(
				(string) $message, '/\%([^\%]+)\%/iu',
				fn($m) => $row?->getValue($m[1]) ?? $m[0],
			),
			$row?->getItem(),
		);

		return $message ?: null;
	}
}
