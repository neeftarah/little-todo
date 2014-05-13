<?php
// web/index.php
define('APP_DIR', dirname(__DIR__));
$littleTodo = require_once(APP_DIR . '/src/app.php');
$littleTodo->run();