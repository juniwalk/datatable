<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use InvalidArgumentException;
use JuniWalk\DataTable\Action;
use Tester\Assert;
use Tester\TestCase;

abstract class AbstractActionCase extends TestCase
{
	/** @var class-string<Action> */
	protected string $className;


	public function testBasics(): void
	{
		$action = $this->createAction('btn', 'Button', [
			'group' => 'action',
		]);

		Assert::same('btn', $action->getName());
		Assert::same('action', $action->getGroup());
		Assert::same('Button', $action->getLabel());
	}


	public function testCondition(): void
	{
		$action = $this->createAction('btn', 'Button');
		Assert::true($action->isAllowed());

		$action->setAllowCondition(fn() => false);
		Assert::false($action->isAllowed());

		$action->setAllowCondition(true);
		Assert::true($action->isAllowed());
	}


	/**
	 * @param  mixed[] $args
	 * @throws InvalidArgumentException
	 */
	protected function createAction(string $name, string $label, array $args = []): Action
	{
		if (!isset($this->className)) {
			throw new InvalidArgumentException('Missing className of the Action');
		}

		$action = new ($this->className)($label, ...$args);

		$presenter = new TestPresenter;
		$presenter->getComponent('table')
			->addAction($name, $action);

		return $action;
	}
}
