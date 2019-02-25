<?php
header('Access-Control-Allow-Origin: *');

// Setup app.
require_once realpath(__DIR__) . '/../bootstrap/init.php';
require_once realpath(__DIR__) . '/../bootstrap/wafMiddleware.php';

/** Load routes **/
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath(__DIR__) . '/../routes'));
$routes = array_keys(array_filter(iterator_to_array($iterator), function($file) {
    return $file->isFile();
}));

foreach ($routes as $route) {
    if (strpos($route, '.php') !== false) {
        require_once(realpath($route));
    }
}

$app->run();
