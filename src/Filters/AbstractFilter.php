<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Filters;

use JuniWalk\DataTable\Filter;
use JuniWalk\Utils\Format;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

abstract class AbstractFilter extends Control implements Filter
{
	protected bool $isFiltered = false;
	protected mixed $value;

	/** @var string[] */
	protected array $columns = [];

	public function __construct(
		protected string $label,
	) {
	}


	public function setColumns(string ...$columns): self
	{
		$this->columns = array_unique(array_filter($columns));
		return $this;
	}


	/**
	 * @return string[]
	 */
	public function getColumns(): array
	{
		return $this->columns ?: [$this->name];
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


	public function format(mixed $value): string
	{
		return Format::stringify($value);
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
