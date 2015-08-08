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

// postback object
$di->set('postback', function() {
   	return new Model\Postback();
});

/**
 * Build app, set routes
 */
$app = new Phalcon\Mvc\Micro($di);

// Matches any route starting with i
$app->post('/(i[a-z\.]+)', function () use ($app) {
	$response = new Phalcon\Http\Response();
	$redis    = $app->di->get('redis');
	$post     = $app->request->getJsonRawBody();

	// verify parameters
	$valid = true;

	// should also make sure a valid method i.e. POST or GET
	if (is_null($post->endpoint->method) || !trim($post->endpoint->method)) {
		$valid = false;
	}
	if (is_null($post->endpoint->url) || !trim($post->endpoint->url)) {
		$valid = false;
	}
	if (is_null($post->data) || !count($post->data)) {
		$valid = false;
	}

	if (!$valid) {
		$response->setStatusCode(400, "Invalid Parameters");
		$response->send();
		die();
	}

	try {

		// use pipeline for batch storage
		$redis->pipeline(function ($pipe) use ($app, $post) {
			foreach ($post->data as $data) {
				$postback = $app->di->get('postback');

				$postback->method = $post->endpoint->method;
				$postback->url    = $post->endpoint->url;
				$postback->data   = $data;

				$pipe->lpush('job-queue', json_encode($postback));
			}
		});		
	} catch (\Exception $e) {
		// unable to connect/write to db
		$response->setStatusCode(500, "Internal Error");
		$response->send();
		die();
	}

	/**
	 *  @todo Error handling for failed writes
	 */

	// send response
	$response->setStatusCode(200, "Success");
	$response->send();
	die();
});

// not found route
$app->notFound(function () use ($app) {
	$response = new Phalcon\Http\Response();
	$response->setStatusCode(404, "Not Found");
	$response->send();
	die();
});

try {
	$app->handle();
} catch (\Exception $e) {
	/**
	 * @todo Log Errors
	 * @todo Return Helpful Message
	 */
}

