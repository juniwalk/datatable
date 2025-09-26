<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use AllowDynamicProperties;
use Nette\Application\UI;

class TemplateFactory implements UI\TemplateFactory
{
	public function createTemplate(?UI\Control $control = null, ?string $class = null): UI\Template
	{
		return new

		#[AllowDynamicProperties]
		class implements UI\Template
		{
			private string $file;

			public function render(): void
			{
			}

			public function setFile(string $file): static
			{
				$this->file = $file;
				return $this;
			}

			public function getFile(): ?string
			{
				return $this->file ?? null;
			}
		};
	}
}
