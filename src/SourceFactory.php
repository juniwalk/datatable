<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable;

use Doctrine\ORM\QueryBuilder;
use JuniWalk\DataTable\Exceptions\SourceUnknownException;
use JuniWalk\DataTable\Sources\ArraySource;
use JuniWalk\DataTable\Sources\DoctrineORMSource;

final class SourceFactory
{
	private function __construct() {}


	/**
	 * @throws SourceUnknownException
	 */
	public static function fromModel(mixed $model): Source
	{
		if ($model instanceof Source) {
			return $model;
		}

		return match (true) {
			$model instanceof QueryBuilder	=> new DoctrineORMSource($model),
			is_array($model)				=> new ArraySource($model),	// @phpstan-ignore argument.type

			default => throw SourceUnknownException::fromModel($model),
		};
	}
}
