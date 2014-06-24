<?php
// web/index_dev.php
error_reporting(E_ALL);

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

require __DIR__.'/../src/Config/dev.php';
require __DIR__.'/../src/app.php';

$app->run();
