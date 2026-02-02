<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Nette\Application\UI\Control;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Bridges\ApplicationLatte\UIExtension;
use Nette\Bridges\FormsLatte\FormsExtension;
use Latte\Engine;

class DefaultLatteFactory implements LatteFactory
{
	public function create(?Control $control = null): Engine
	{
		$engine = new Engine;
		$engine->addExtension(new UIExtension($control));
		$engine->addExtension(new TranslatorExtension);
		$engine->addExtension(new FormsExtension);

		return $engine;
	}
}
