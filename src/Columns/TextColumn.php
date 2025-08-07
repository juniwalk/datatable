<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Columns\Interfaces\CustomRenderer;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Row;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Stringable;

class TextColumn extends AbstractColumn implements Sortable, Filterable, CustomRenderer
{
	use Traits\Sorting;
	use Traits\Filters;
	use Traits\Renderer;

	protected ?int $truncate = null;


	public function setTruncate(?int $truncate): self
	{
		$this->truncate = $truncate;
		return $this;
	}


	public function getTruncate(): ?int
	{
		return $this->truncate;
	}


	/**
	 * @throws FieldInvalidException
	 */
	protected function renderValue(Row $row): Html|string
	{
		if (!$value = $row->getValue($this)) {
			return '';
		}

		if ($value instanceof Stringable) {
			$value = (string) $value;
		}

		if (!is_scalar($value)) {
			throw FieldInvalidException::fromColumn($this, $value, 'string');
		}

		$value = (string) $value;

		if ($this->truncate > 0) {
			$value = Strings::truncate($value, $this->truncate);
		}

		return $value;
	}
}
