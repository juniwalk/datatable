<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

use JuniWalk\DataTable\Enums\Align;
use Tester\Environment;

define('ProcessId', getmypid());

if (@!include __DIR__.'/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

Environment::setup();

const ItemsData = [
	['id' => 1, 'name' => 'John Doe', 'height' => 186.5, 'birth' => new DateTime('1990-12-16'), 'align' => Align::Left, 'order' => 3],
	['id' => 2, 'name' => 'Jane Doe', 'height' => 172.3, 'birth' => new DateTime('1994-04-01'), 'align' => Align::Right, 'order' => 1],
	['id' => 3, 'name' => 'Jack Doe', 'height' => 191.4, 'birth' => new DateTime('1982-11-11'), 'align' => Align::Left, 'order' => 2],
	['id' => 4, 'name' => 'Jenna Doe', 'height' => 167.9, 'birth' => new DateTime('1963-03-18'), 'align' => Align::Center, 'order' => 0],
	['id' => 5, 'name' => 'Jimmy Doe', 'height' => 178.6, 'birth' => new DateTime('1964-04-14'), 'align' => Align::Left, 'order' => 4],
];
