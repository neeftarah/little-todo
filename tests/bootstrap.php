<?php

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Silex\Tests', __DIR__);

if (!class_exists('Silex\Provider\TwigServiceProvider')) {
    echo "You must install Twig and twig-bridge:\n";
    exit(1);
}
