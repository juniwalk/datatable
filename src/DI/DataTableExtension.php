<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\DI;

use Contributte\Translation\DI\TranslationProviderInterface as TranslationProvider;
use Nette\DI\CompilerExtension;

final class DataTableExtension extends CompilerExtension implements TranslationProvider
{
	public function getTranslationResources(): array
	{
		return [__DIR__.'/../../locale'];
	}
}
