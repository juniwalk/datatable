<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use JuniWalk\DataTable\Table;
use JuniWalk\Utils\Enums\Casing;
use JuniWalk\Utils\Format;
use Nette\Application\PresenterFactory;
use Nette\Application\Routers\SimpleRouter;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
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
		$latteFactory = new DefaultLatteFactory;

		$this->setParent(null, $name);
		$this->injectPrimary(
			httpRequest: $httpRequest,
			httpResponse: $httpResponse,
			router: new SimpleRouter,
			session: $session,

			presenterFactory: new PresenterFactory,
			templateFactory: new TemplateFactory(
				$latteFactory,
				$httpRequest,
			),
		);
	}


	protected function createComponentTable(): Table
	{
		return new Table;
	}


	protected function createComponentTableTest(): Table
	{
		return new TestTable;
	}
}
