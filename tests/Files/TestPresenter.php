<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use JuniWalk\DataTable\Sources\ArraySource;
use JuniWalk\DataTable\Table;
use JuniWalk\Utils\Enums\Casing;
use JuniWalk\Utils\Format;
use Nette\Application\PresenterFactory;
use Nette\Application\Routers\SimpleRouter;
use Nette\Application\UI\Presenter;
use Nette\Http;

class TestPresenter extends Presenter
{
	public function __construct()
	{
		$url = new Http\UrlScript('http://localhost/index.php', '/index.php');
		$name = Format::className($this, Casing::Pascal, 'Presenter');

		$httpRequest = new Http\Request($url);
		$httpResponse = new Http\Response;
		$session = new Http\Session($httpRequest, $httpResponse);

		$this->setParent(null, $name);
		$this->injectPrimary(
			httpRequest: $httpRequest,
			httpResponse: $httpResponse,
			router: new SimpleRouter,
			session: $session,

			presenterFactory: new PresenterFactory,
			templateFactory: new TemplateFactory,
		);
	}


	protected function createComponentTable(): Table
	{
		return new Table;
	}


	protected function createComponentTableWithSource(): Table
	{
		$table = new Table;
		$table->setSource(new ArraySource(ItemsData));

		$table->addColumnOrder('order', 'Order')->setSortable(true);
		$table->addColumnText('name', 'Name');
		$table->addColumnNumber('id', '#')->setSortable(true);

		$table->addFilterText('name', 'Name');

		return $table;
	}


	protected function createComponentTableExtended(): Table
	{
		return new class extends Table {
			protected function createModel(): mixed {
				return ItemsData;
			}

			protected function createTable(): void {
				$this->addColumnOrder('order', 'Order');
				$this->addColumnText('name', 'Name');
				$this->addColumnNumber('height', 'Height');
				$this->addColumnNumber('id', '#');
			}
		};
	}
}
