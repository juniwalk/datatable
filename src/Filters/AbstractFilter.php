<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use Closure;
use JuniWalk\DataTable\Column;
use JuniWalk\DataTable\Columns\Interfaces\Filterable;
use JuniWalk\DataTable\Filter;
use JuniWalk\DataTable\Table;
use JuniWalk\DataTable\Traits;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\Strings;
use Nette\Application\UI\Component;
use Nette\ComponentModel\IContainer;

abstract class AbstractFilter extends Component implements Filter
{
	use Traits\Translation;

	/** @var array<string, Column> */
	protected array $columns;

	protected ?Closure $condition = null;
	protected bool $isFiltered = false;


	public function __construct(
		protected string $label,
	) {
	}


	public function getType(): string
	{
		return Format::className($this);
	}


	public function getLabel(): string
	{
		return $this->label;
	}


	public function isFiltered(): bool
	{
		return $this->isFiltered;
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


	public function applyCondition(mixed $model): bool
	{
		if (!$this->isFiltered || !isset($this->condition)) {
			return false;
		}

		return (bool) call_user_func($this->condition, $model, $this->getValue());
	}


	public function fieldName(): string
	{
		return Format::camelCase(Strings::webalize($this->name));
	}


	protected function validateParent(IContainer $parent): void
	{
		parent::validateParent($parent);

		$this->monitor($this::class, fn() => $this->lookup(Table::class));
		$this->monitor(Table::class, function(Table $table) {
			$this->setTranslator($table->getTranslator());
		});
	}
}
