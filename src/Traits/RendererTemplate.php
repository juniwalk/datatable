<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Interfaces\TemplateRenderable;
use JuniWalk\DataTable\Row;
use Throwable;

/**
 * @phpstan-require-implements TemplateRenderable
 */
trait RendererTemplate
{
	protected ?string $templateFile = null;
	protected bool $strictRender = false;


	public function setTemplateFile(?string $templateFile, bool $strict = false): static
	{
		if ($templateFile && !file_exists($templateFile)) {
			throw new \Exception;
		}

		$this->strictRender = $strict;
		$this->templateFile = $templateFile;
		return $this;
	}


	public function getTemplateFile(): ?string
	{
		return $this->templateFile;
	}


	public function hasTemplateFile(): bool
	{
		return isset($this->templateFile);
	}


	public function renderTemplate(Row $row, mixed ...$params): void
	{
		try {
			echo $this->templateRender($row, ...$params);

		} catch (Throwable $e) {
			$this->strictRender && throw $e;
		}
	}


	/**
	 * @throws InvalidStateException
	 */
	public function templateRender(Row $row, mixed ...$params): ?string
	{
		if (!isset($this->templateFile)) {
			throw InvalidStateException::customRendererMissing($this, 'template');
		}

		$template = $this->getTemplate();
		$template->setFile($this->templateFile);
		$template->item = $row->getItem();

		foreach ($params as $key => $value) {
			if (is_int($key)) {
				$key = 'param'.$key;
			}

			$template->$key = $value;
		}

		try {
			ob_start(fn() => '');
			$template->render();

			return ob_get_clean() ?: null;

		} catch (Throwable $e) {
			ob_end_clean();
			throw $e;
		}
	}
}
