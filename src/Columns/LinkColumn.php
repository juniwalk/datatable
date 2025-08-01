<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Traits\Linking;
use JuniWalk\DataTable\Exceptions\FieldNotFoundException;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;

/**
 * @phpstan-import-type LinkArgs from Linking
 */
class LinkColumn extends TextColumn
{
	use Linking;

	protected string $dest;

	/** @var LinkArgs */
	protected array $args = [];


	/**
	 * @param LinkArgs $args
	 */
	public function setLink(string $dest, array $args = []): self
	{
		$this->dest = $dest;
		$this->args = $args;
		return $this;
	}


	/**
	 * @throws FieldInvalidException
	 * @throws InvalidLinkException
	 */
	protected function renderValue(Row $row): Html|string
	{
		$link = $this->createLink($this->dest, $this->buildLinkArgs($row));
		$value = (string) parent::renderValue($row);

		return Html::el('a', $value)->setHref($link)
			->addClass('fw-bold');
	}


	/**
	 * @return LinkArgs
	 * @throws FieldNotFoundException
	 */
	private function buildLinkArgs(Row $row): array
	{
		$args = $this->args;

		foreach ($args as $key => $arg) {
			if (!is_string($arg) || !str_starts_with($arg, '@')) {
				continue;
			}

			$args[$key] = $row->getValue(substr($arg, 1));
		}

		$args[$row->getPrimaryKey()] ??= $row->getId();
		return $args;
	}
}
