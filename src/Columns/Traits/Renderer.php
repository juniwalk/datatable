<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns\Traits;

use Closure;
use JuniWalk\DataTable\Columns\Interfaces\CustomRenderer;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Row;
use Nette\Utils\Html;

/**
 * @phpstan-require-implements CustomRenderer
 */
trait Renderer
{
	private ?Closure $renderer = null;


	public function setRenderer(?Closure $renderer = null): self
	{
		$this->renderer = $renderer;
		return $this;
	}


	public function getRenderer(): ?Closure
	{
		return $this->renderer;
	}


	public function hasRenderer(): bool
	{
		return is_callable($this->renderer);
	}


	/**
	 * @throws InvalidStateException
	 */
	public function renderCustom(Row $row, Html|string $value): mixed
	{
		if (!is_callable($this->renderer)) {
			throw InvalidStateException::columnCustomRendererMissing($this);
		}

		return call_user_func($this->renderer, $row->getItem(), $value);
	}
}
