<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Interfaces;

use JuniWalk\DataTable\Row;

interface TemplateRenderable
{
	public function setTemplateFile(?string $templateFile): static;
	public function getTemplateFile(): ?string;
	public function hasTemplateFile(): bool;

	public function renderTemplate(Row $row, mixed ...$params): void;
	public function templateRender(Row $row, mixed ...$params): ?string;
}
