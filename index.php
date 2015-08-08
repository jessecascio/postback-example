<?php

use Phalcon\Mvc\Micro;

$app = new Micro();

/**
 * Matches any route starting with i
 */
$app->get('/(i[a-z\.]+)', function () {

});

/**
 * Default route handler
 */
$app->notFound(function () use ($app) {

});

$app->handle();
