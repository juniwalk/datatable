<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns\Traits;

use Closure;
use JuniWalk\DataTable\Columns\Interfaces\CustomRenderer;
use JuniWalk\DataTable\Row;

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


	public function renderCustom(Row $row): mixed
	{
		if (!is_callable($this->renderer)) {
			// todo: throw ColumnRendererException
			throw new \Exception;
		}

		return call_user_func($this->renderer, $row->getItem());
	}
}
