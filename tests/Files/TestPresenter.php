<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use JuniWalk\DataTable\Table;
use Nette\Application\PresenterFactory;
use Nette\Application\Routers\SimpleRouter;
use Nette\Application\UI\Presenter;
use Nette\Http;

class TestPresenter extends Presenter
{
	public function __construct()
	{
		$url = new Http\UrlScript('http://localhost/index.php', '/index.php');
		$request = new Http\Request($url);
		$response = new Http\Response;

		$this->setParent(null, 'Test');
		$this->injectPrimary(
			$request,
			$response,
			new PresenterFactory,
			new SimpleRouter,
			new Http\Session($request, $response),
		);
	}


	protected function createComponentTable(): Table
	{
		return new Table;
	}
}
