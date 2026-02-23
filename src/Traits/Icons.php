<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Html as CustomHtml;
use Nette\Utils\Html;

trait Icons
{
	protected ?string $icon = null;
	protected ?Color $iconColor = null;
	protected bool $iconFixed = true;


	public function setIcon(?string $icon, bool $fixedWidth = true, ?Color $color = null): static
	{
		$this->iconFixed = $fixedWidth;
		$this->iconColor = $color;
		$this->icon = $icon;
		return $this;
	}


	public function getIcon(): ?string
	{
		return $this->icon;
	}


	public function getIconColor(): ?Color
	{
		return $this->iconColor;
	}


	public function isIconFixedWith(): bool
	{
		return $this->iconFixed;
	}


	protected function createIcon(): ?Html
	{
		if (!isset($this->icon)) {
			return null;
		}

		return CustomHtml::icon($this->icon, $this->iconFixed, $this->iconColor);
	}
}
