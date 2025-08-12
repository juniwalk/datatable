<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use Closure;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Exceptions\InvalidStateException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Table;
use JuniWalk\DataTable\Traits;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\Strings;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
use Nette\Application\UI\Form;

abstract class AbstractFilter extends Control implements Filter
{
	use Traits\LinkHandler;
	use Traits\Translation;

	protected bool $isFiltered = false;
	protected mixed $value;

	protected ?Closure $condition = null;

	/** @var array<string, Column> */
	protected array $columns;


	public function __construct(
		protected string $label,
	) {
	}


	public function setColumns(Column ...$columns): self
	{
		$this->columns = [];

		foreach ($columns as $column) {
			if (!$column instanceof Filterable) {
				continue;
			}

			$this->columns[$column->getName()] = $column->addFilter($this);
		}

		return $this;
	}


	/**
	 * @return array<string, Column>
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}


	public function hasColumn(string $columnName): bool
	{
		return array_key_exists($columnName, $this->columns);
	}


	public function setCondition(?Closure $condition): self
	{
		$this->condition = $condition;
		return $this;
	}


	public function hasCondition(): bool
	{
		return isset($this->condition);
	}


	public function applyCondition(mixed $model): bool
	{
		if (!$this->isFiltered || !isset($this->condition)) {
			return false;
		}

		return (bool) call_user_func($this->condition, $model, $this->value ?? null);
	}


	public function setValue(mixed $value): self
	{
		$this->isFiltered = $value !== '' && $value !== null;
		$this->value = $value;

		return $this;
	}


	public function getValue(): mixed
	{
		return $this->value ?? null;
	}


	public function isFiltered(): bool
	{
		return $this->isFiltered;
	}


	public function format(mixed $value): ?string
	{
		return Format::stringify($value) ?: null;
	}


	/**
	 * @throws InvalidStateException
	 */
	public function render(Form $form): void
	{
		if (!$input = $form->getComponent($this->fieldName(), false)) {
			throw InvalidStateException::filterInputMissing($this);
		}

		$className = Format::className($this, suffix: 'Filter');
		$clearLink = $this->createLink('clear!', ['column' => $this->name]);

		/** @var \Nette\Bridges\ApplicationLatte\DefaultTemplate */
		$template = $this->getTemplate();
		$template->setFile(__DIR__.'/../templates/filter-'.$className.'.latte');

		$template->add('isFiltered', $this->isFiltered);
		$template->add('clearLink', $clearLink);
		$template->add('label', $this->translate($this->label));
		$template->add('name', $this->fieldName());
		$template->add('input', $input);

		$template->render();
	}


	/**
	 * @throws InvalidStateException
	 */
	protected function validateParent(IContainer $table): void
	{
		if (!$table instanceof Table) {
			throw InvalidStateException::parentRequired(Table::class, $this);
		}

		$this->setTranslator($table->getTranslator());
		parent::validateParent($table);
	}


	protected function fieldName(): string
	{
		return Format::camelCase(Strings::webalize($this->name));
	}
}
