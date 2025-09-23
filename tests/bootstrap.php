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
