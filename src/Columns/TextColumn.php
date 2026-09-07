<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Columns;

use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Columns\Interfaces\Hideable;
use JuniWalk\DataTable\Columns\Interfaces\Sortable;
use JuniWalk\DataTable\Columns\Traits\Filters;
use JuniWalk\DataTable\Columns\Traits\Hiding;
use JuniWalk\DataTable\Columns\Traits\Sorting;
use JuniWalk\DataTable\Exceptions\FieldInvalidException;
use JuniWalk\DataTable\Interfaces\CallbackRenderable;
use JuniWalk\DataTable\Row;
use JuniWalk\DataTable\Tools\FormatValue;
use JuniWalk\DataTable\Traits\RendererCallback;
use Nette\Utils\Html;
use Nette\Utils\Strings;

use function is_scalar;

class TextColumn extends AbstractColumn implements Sortable, Filterable, Hideable, CallbackRenderable
{
	use Sorting, Filters, Hiding, RendererCallback;

	protected ?int $truncate = null;


	public function setTruncate(?int $truncate): static
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
	protected function formatValue(Row $row): Html|string
	{
		$value = $row->getValue($this);

		// ? Allow empty values to not throw an exception
		if ($value === null || $value === '') {
			return '';
		}

		$value = FormatValue::string($value);

		if (!is_scalar($value)) {
			throw FieldInvalidException::fromColumn($this, $value, 'string');
		}

		if ($this->truncate > 0) {
			$value = Strings::truncate($value, $this->truncate);
		}

		return $value;
	}
}
