<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Tools;

use Nette\Application\UI\Template;
use Throwable;

class Output
{
	private function __construct() {}


	/**
	 * ? Capture output of given callback and return it as string
	 * @throws Throwable
	 */
	public static function capture(callable $callback): ?string
	{
		try {
			ob_start(fn() => '');

			$callback();

			return ob_get_clean() ?: null;

		} catch (Throwable $e) {
			ob_end_clean();
			throw $e;
		}
	}


	/**
	 * ? Capture rendered Template class to string
	 * @param mixed[] $params
	 */
	public static function captureTemplate(Template $template, array $params = []): ?string
	{
		foreach ($params as $key => $value) {
			if (is_numeric($key)) {
				$key = 'param'.$key;
			}

			$template->$key = $value;
		}

		return static::capture(fn() => $template->render());
	}
}
