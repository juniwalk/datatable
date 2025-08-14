<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use Closure;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Interfaces\CustomRenderer;
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
	 * @return ($return is true ? Html|string|null : null)
	 * @throws FieldInvalidException
	 * @throws InvalidStateException
	 */
	public function renderCustom(Row $row, Html|string $value = '', bool $return = false): Html|string|null
	{
		if (!is_callable($this->renderer)) {
			throw InvalidStateException::customRendererMissing($this);
		}

		$content = call_user_func($this->renderer, $row->getItem(), $value);

		if (!is_null($content) && !(is_string($content) || $content instanceof Html)) {
			throw FieldInvalidException::fromComponent($this, $content, 'Html|string|null');
		}
	
		if ($return === true) {
			return $content;
		}

		echo $content; return null;
	}
}
