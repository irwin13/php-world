<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/', function () {
    echo "HOMEPAGE";
});

$app->get('/hi', function () {
    echo "HIHIHI";
});

$app->get('/hello', function () {
    echo "Hello";
});

$app->run();
