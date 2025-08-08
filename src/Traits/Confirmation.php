<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Row;
use Nette\Utils\Strings;

trait Confirmation
{
	protected ?string $confirmMessage = null;


	public function setConfirmMessage(?string $confirmMessage): static
	{
		$this->confirmMessage = $confirmMessage;
		return $this;
	}


	public function getConfirmMessage(): ?string
	{
		return $this->confirmMessage;
	}


	public function createConfirm(?Row $row): ?string
	{
		if (!$message = $this->confirmMessage) {
			return null;
		}

		// todo: translate

		if (isset($row)) {
			$message = Strings::replace(
				$message, '/\%([^\%]+)\%/iu',
				fn($m) => $row->getValue($m[1])
			);
		}

		return $message;
	}
}
