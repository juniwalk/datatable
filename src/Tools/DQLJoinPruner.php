<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Tools;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Stringable;

use function array_keys;
use function is_array;
use function preg_match;
use function preg_quote;
use function sprintf;

final class DQLJoinPruner
{
	private const AliasFieldPattern = '/(?<![A-Za-z0-9_])%s\s*\./';
	private const AliasBarePattern = '/(?<![A-Za-z0-9_])%s(?![A-Za-z0-9_])/';
	private const ParameterPattern = '/(?<![A-Za-z0-9_])(?::[A-Za-z_]\w*|\?[0-9]*)/';


	private function __construct() { }


	/**
	 * @throws LogicException If a retained join does not have an alias.
	 */
	public static function prune(QueryBuilder $query): void
	{
		$joinParts = self::getJoinPartsNormalized($query);
		$joins = [];

		if ($joinParts === null) {
			return;
		}

		foreach ($joinParts as $rootJoins) {
			foreach ($rootJoins as $join) {
				$alias = $join->getAlias();
				$joins[$alias] = $join;
			}
		}

		if (!$joins) {
			return;
		}

		$aliases = array_keys($joins);

		$references =  self::getReferencedJoinAliases($aliases, [$query->getDQLPart('select')], true);
		$references += self::getReferencedJoinAliases($aliases, [
			$query->getDQLPart('where'),
			$query->getDQLPart('groupBy'),
			$query->getDQLPart('having'),
			$query->getDQLPart('orderBy'),
			$query->getDQLPart('set'),
		]);

		foreach ($joins as $alias => $join) {
			if ($join->getJoinType() !== Join::LEFT_JOIN || self::joinHasParameters($join)) {
				$references[$alias] = true;
			}
		}

		do {
			$changed = false;

			foreach (array_keys($references) as $alias) {
				if (!isset($joins[$alias])) {
					continue;
				}

				$join = $joins[$alias];
				$dependencies = self::getReferencedJoinAliases($aliases, [
					$join->getJoin(),
					$join->getCondition(),
					$join->getIndexBy(),
				]);

				foreach (array_keys($dependencies) as $dependency) {
					if (isset($references[$dependency])) {
						continue;
					}

					$references[$dependency] = true;
					$changed = true;
				}
			}

		} while ($changed);

		$keptJoins = [];
		$removedJoin = false;

		foreach ($joinParts as $rootAlias => $rootJoins) {
			foreach ($rootJoins as $join) {
				$alias = $join->getAlias();

				if ($alias === null || isset($references[$alias])) {
					$keptJoins[$rootAlias][] = $join;
					continue;
				}

				$removedJoin = true;
			}
		}

		if (!$removedJoin) {
			return;
		}

		$query->resetDQLPart('join');

		foreach ($keptJoins as $rootJoins) {
			foreach ($rootJoins as $join) {
				$alias = $join->getAlias();

				if ($alias === null) {
					throw new LogicException('A retained join must have an alias.');
				}

				$args = [
					$join->getJoin(),
					$alias,
					$join->getConditionType(),
					$join->getCondition(),
					$join->getIndexBy(),
				];

				if ($join->getJoinType() === Join::LEFT_JOIN) {
					$query->leftJoin(...$args);

				} else {
					$query->innerJoin(...$args);
				}
			}
		}
	}


	/**
	 * @return array<string, list<Join>>|null
	 */
	public static function getJoinPartsNormalized(QueryBuilder $query): ?array
	{
		$joinParts = $query->getDQLPart('join');
		$joins = [];

		if (!is_array($joinParts) || !$joinParts) {
			return null;
		}

		foreach ($joinParts as $rootAlias => $rootJoins) {
			if (!is_string($rootAlias) || !is_array($rootJoins)) {
				return null;
			}

			foreach ($rootJoins as $join) {
				if (!$join instanceof Join || $join->getAlias() === null) {
					return null;
				}

				$joins[$rootAlias][] = $join;
			}
		}

		return $joins;
	}


	/**
	 * @param  string[] $aliases
	 * @param  mixed[] $parts
	 * @return array<string, true>
	 */
	private static function getReferencedJoinAliases(array $aliases, array $parts, bool $matchBareAliases = false): array
	{
		$references = [];

		foreach ($parts as $part) {
			if (is_array($part)) {
				$references += self::getReferencedJoinAliases($aliases, $part, $matchBareAliases);
				continue;
			}

			if (!is_string($part) && !$part instanceof Stringable) {
				continue;
			}

			$dql = (string) $part;

			foreach ($aliases as $alias) {
				$quotedAlias = preg_quote($alias, '/');

				if (preg_match(sprintf(self::AliasFieldPattern, $quotedAlias), $dql)
				|| ($matchBareAliases && preg_match(sprintf(self::AliasBarePattern, $quotedAlias), $dql))
				) {
					$references[$alias] = true;
				}
			}
		}

		return $references;
	}


	private static function joinHasParameters(Join $join): bool
	{
		return ($condition = $join->getCondition()) !== null
			&& preg_match(self::ParameterPattern, (string) $condition) === 1;
	}
}
