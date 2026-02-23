<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use Closure;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Interfaces\CallbackRenderable;
use JuniWalk\DataTable\Row;
use Nette\Utils\Html;
use Throwable;

/**
 * @phpstan-require-implements CallbackRenderable
 */
trait RendererCallback
{
	protected ?Closure $renderer = null;
	protected bool $strictRender = false;


	public function setRenderer(?Closure $renderer, bool $strict = false): static
	{
		$this->strictRender = $strict;
		$this->renderer = $renderer;
		return $this;
	}


	public function getRenderer(): ?Closure
	{
		return $this->renderer;
	}


	public function hasRenderer(): bool
	{
		return isset($this->renderer);
	}


	public function renderCallback(Row $row, mixed ...$params): void
	{
		try {
			echo $this->callbackRender($row, ...$params);

		} catch (InvalidStateException $e) {
		}

		if ($this->strictRender && isset($e)) {
			throw $e;
		}
	}


	/**
	 * @throws FieldInvalidException
	 * @throws InvalidStateException
	 */
	public function callbackRender(Row $row, mixed ...$params): ?Html
	{
		if (!isset($this->renderer)) {
			throw InvalidStateException::customRendererMissing($this, 'callback');
		}

		$html = call_user_func($this->renderer, $row->getItem(), ...$params);

		if (empty($html)) {
			return null;
		}

		if (!is_string($html) && !$html instanceof Html) {
			throw FieldInvalidException::fromComponent($this, $html, 'Html|string|null');
		}

		return Html::el()->setHtml($html);
	}
}
