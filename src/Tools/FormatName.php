<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Tools;

use JuniWalk\Utils\Format;
use JuniWalk\Utils\Strings;

class FormatName
{
	private function __construct() {}


	public static function component(string $name): string
	{
		$name = Format::kebabCase($name);
		$name = Strings::webalize($name);
		return Format::camelCase($name);
	}
}
