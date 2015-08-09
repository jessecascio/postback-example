<?php

// load config, set up autoloading
include_once 'config.php';
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

// poststore service
$di->set('poststore', function() use ($di) {
	// constructor injection
   	return new Service\PostStore($di->get('redis'));
});

// rawpost model
$di->set('rawpost', function() {
   	return new Model\RawPost();
});

/**
 * Build app, set routes
 */
$app = new Phalcon\Mvc\Micro($di);

// Matches any route starting with i
$app->post('/(i[a-z\.]+)', function () use ($app) {
	$response = new Phalcon\Http\Response();
	
	// collect json data
	$rawpost = $app->di->get('rawpost');
	$rawpost->setRawpost($app->request->getJsonRawBody(true));

	// set up storage service
	$poststore = $app->di->get('poststore');
	$poststore->setRawPost($rawpost);
	
	// make sure post is valid	
	if (!$poststore->isValid()) {
		$response->setStatusCode(400, $poststore->getError());
		$response->send();
		die();
	}

	// store the posts
	if (!$poststore->storePosts()) {
		// log internal error message
		$response->setStatusCode(500, "Internal Error");
		$response->send();
		die();
	}

	// success
	$response->setStatusCode(200, "Success");
	$response->send();
	die();
});

// no found route
$app->notFound(function () use ($app) {
	$response = new Phalcon\Http\Response();
	$response->setStatusCode(404, "Not Found");
	$response->send();
	die();
});

try {
	$app->handle();
} catch (\Exception $e) {
	// log internal error message
	$response = new Phalcon\Http\Response();
	$response->setStatusCode(500, "Internal Error");
	$response->send();
}

