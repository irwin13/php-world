<?php
require 'vendor/autoload.php';
require 'vendor/slim/views/Smarty.php';

$app = new \Slim\Slim(array(
		'view' => new \Slim\Views\Smarty(),
		'debug' => true,
		'log.enable' => true,
		'log.path' => 'logs/',
		'log.level' => 4,
		'mode' => 'development'
	)
);

$app->get('/', function () use ($app) {
    $app->render('view/homepage.tpl.html');
});

require_once 'controller.php';

$app->run();
