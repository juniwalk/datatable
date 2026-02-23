<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Interfaces\TemplateRenderable;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Tools\Output;
use Throwable;

/**
 * @phpstan-require-implements TemplateRenderable
 */
trait RendererTemplate
{
	/** @var array<string, mixed> */
	protected ?array $templateParams = null;
	protected ?string $templateFile = null;
	protected bool $strictRender = true;


	/**
	 * @param  array<string, mixed> $params
	 * @throws InvalidStateException
	 */
	public function setTemplateParams(?array $params): static
	{
		if (isset($params['item'])) {
			throw InvalidStateException::customParamReserved($this, 'item');
		}

		$this->templateParams = $params;
		return $this;
	}


	/**
	 * @throws InvalidStateException
	 */
	public function setTemplateFile(?string $templateFile, bool $strict = true): static
	{
		if ($templateFile && !file_exists($templateFile)) {
			throw InvalidStateException::customRendererMissing($this, 'template');
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

		} catch (InvalidStateException $e) {
		}

		if ($this->strictRender && isset($e)) {
			throw $e;
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

		$params = array_merge($params, $this->templateParams ?? [], [
			'item' => $row->getItem(),
		]);

		return Output::captureTemplate($template, $params);
	}
}
