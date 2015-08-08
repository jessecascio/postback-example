<?php

// load config, set up autoloading
require_once 'config.php';
require_once 'vendor/autoload.php';

/**
 * Set up the dependency injector
 */
$di = new Phalcon\DI\FactoryDefault();

// Redis client
$di->set('redis', function() {
    $ip   = defined('REDIS_IP')   ? REDIS_IP   : '127.0.0.1';
    $port = defined('REDIS_PORT') ? REDIS_PORT : '6379';

   	return new Predis\Client('tcp://'.$ip.':'.$port);
});

/**
 * Build app, set routes
 */
$app = new Phalcon\Mvc\Micro($di);

/**
 * Matches any route starting with i
 */
$app->get('/(i[a-z\.]+)', function () use ($app) {
	// $app->di->get('redis')
});

/**
 * Default route handler
 */
$app->notFound(function () use ($app) {

});

try {
	$app->handle();
} catch (\Exception $e) {
	/**
	 * @todo Log Errors
	 * @todo Return Helpful Message
	 */
}

