<?php

/** Composer Autoloader **/
require_once realpath(__DIR__) . '/../vendor/autoload.php';

/** Settings **/
$settings = [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => true, // Allow the web server to send the content-length header
    ],
];

// Initiate Slim App
$app = new \Slim\App($settings);

$container = $app->getContainer();

$container['proxy'] = function($container) {
    $x = new \stdClass();
    $x->url = getenv('PROXY_URL');

    return $x;
};

$container['proxy'] = function($container) {
    $x = new \stdClass();
    $x->url = getenv('PROXY_URL');

    return $x;
};

$container['logLevel'] = function($container) {
    $l = getenv('LOG_LEVEL');
    if ( $l == 'DEBUG') {
       return \Monolog\Logger::DEBUG;
    }

    return \Monolog\Logger::INFO;
};



$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        $r = [
            'code' => 404,
            'status' => 'Not Found',
            'data' => 'Invalid endpoint or resource.'
        ];

        return $response->withJson($r, 404);
    };
};

$container['errorHandler'] = function ($container) {
    return new \Vesica\Waf\Exceptions\Handler();
};



