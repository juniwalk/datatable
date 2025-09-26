<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

use Tester\Environment;

define('ProcessId', getmypid());

if (@!include __DIR__.'/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

Environment::setup();

const ItemsData = [
	['id' => 1, 'name' => 'John Doe', 'height' => 186.5],
	['id' => 2, 'name' => 'Jane Doe', 'height' => 172.3],
	['id' => 3, 'name' => 'Jack Doe', 'height' => 191.4],
	['id' => 4, 'name' => 'Jenna Doe', 'height' => 167.9],
	['id' => 5, 'name' => 'Jimmy Doe', 'height' => 178.6],
];
