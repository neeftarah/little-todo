<?php
// web/index_dev.php
ini_set('display_errors', 1);
error_reporting(-1);

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

require __DIR__.'/../src/config/dev.php';
require __DIR__.'/../src/app.php';

$app->run();