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
		if (preg_match('#^[a-zA-Z0-9_]+$#D', $name)) {
			return $name;
		}

		$name = Format::kebabCase($name);
		$name = Strings::webalize($name);
		return Format::camelCase($name);
	}
}
