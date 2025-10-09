<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use Closure;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Exceptions\FilterInvalidException;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Filters\Interfaces\FilterList;
use JuniWalk\DataTable\Filters\Interfaces\FilterRange;
use JuniWalk\DataTable\Filters\Interfaces\FilterSingle;
use JuniWalk\DataTable\Table;
use JuniWalk\DataTable\Traits;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\Strings;
use Nette\Application\UI\Component;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Form;

/**
 * @phpstan-import-type FilterStruct from Filter
 */
abstract class AbstractFilter extends Component implements Filter
{
	use Traits\Translation;

	protected ?Closure $condition = null;
	protected bool $isFiltered = false;
	protected ?string $field = null;

	/** @var array<string, Column> */
	protected array $columns;


	public function __construct(
		protected string $label,
	) {
	}


	public function getLabel(): string
	{
		return $this->label;
	}


	public function getType(): string
	{
		return Format::className($this, suffix: 'Filter');
	}


	public function isFiltered(): bool
	{
		return $this->isFiltered;
	}


	/**
	 * @return string[]
	 */
	public function getFields(): array
	{
		$fields = [];

		foreach ($this->columns as $name => $column) {
			$fields[$name] = $column->getField() ?? $name;
		}

		if ($this->field) {
			$fields[$this->getName()] = $this->field;
		}

		return $fields;
	}


	public function setField(?string $field): static
	{
		$this->field = $field;
		return $this;
	}


	public function getField(): ?string
	{
		return $this->field;
	}


	public function setColumns(Column ...$columns): static
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


	public function hasColumn(string $name): bool
	{
		return isset($this->columns[$name]);
	}


	public function setCondition(?Closure $condition): static
	{
		$this->condition = $condition;
		return $this;
	}


	public function hasCondition(): bool
	{
		return isset($this->condition);
	}


	/**
	 * @throws FilterInvalidException
	 */
	public function applyCondition(mixed $model): bool
	{
		if (!$this->isFiltered || !isset($this->condition)) {
			return false;
		}

		return (bool) match (true) {
			$this instanceof FilterList,
			$this instanceof FilterSingle => call_user_func($this->condition, $model, $this->getValue()),
			$this instanceof FilterRange  => call_user_func($this->condition, $model, $this->getValueFrom(), $this->getValueTo()),

			default => throw FilterInvalidException::missingImplement($this),
		};
	}


	public function firstInput(Form $form): IComponent
	{
		return $form->getComponent($this->fieldName());
	}


	public function fieldName(): string
	{
		return Format::camelCase(Strings::webalize($this->name));
	}


	protected function validateParent(IContainer $parent): void
	{
		$this->monitor(Table::class, function(Table $table) {
			$this->setTranslator($table->getTranslator());
		});

		parent::validateParent($parent);

		$this->onAnchor[] = function() {
			$this->lookup(Table::class);
		};
	}
}
