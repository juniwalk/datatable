<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Filter;
use JuniWalk\Utils\Format;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

/**
 * @phpstan-import-type ColumnName from Column
 */
abstract class AbstractFilter extends Control implements Filter
{
	protected bool $isFiltered = false;
	protected mixed $value;

	/**
	 * @var ColumnName[]
	 */
	protected array $columns = [];

	public function __construct(
		protected ?string $label,
	) {
	}


	public function getConditions(): array
	{
		$columns = $this->columns ?: [$this->name];
		$condition = [];

		foreach ($columns as $column) {
			$condition[$column] = $this->value;
		}

		return $condition;
	}


	public function setColumns(string ...$columns): self
	{
		$this->columns = array_filter($columns);
		return $this;
	}

	/**
	 * @return ColumnName[]
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}


	public function setFiltered(bool $filtered): self
	{
		$this->isFiltered = $filtered;
		return $this;
	}


	public function isFiltered(): bool
	{
		return $this->isFiltered;
	}


	public function setValue(mixed $value): self
	{
		$this->value = $value;
		return $this;
	}


	public function getValue(): mixed
	{
		return $this->value ?? null;
	}


	public function render(Form $form): void
	{
		if (!$input = $form->getComponent($this->name)) {
			// todo: throw FilterInputException
			throw new \Exception;
		}

		$className = Format::className($this, suffix: 'Filter');

		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->getTemplate();
		$template->setFile(__DIR__.'/../templates/filter-'.$className.'.latte');

		$template->add('label', $this->label);
		$template->add('name', $this->name);
		$template->add('input', $input);

		$template->render();
	}
}
