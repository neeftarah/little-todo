<?php

define('PATH_APP', dirname(dirname(__DIR__)));
define('PATH_WEB', PATH_APP . '/web');
define('PATH_SRC', PATH_APP . '/src');
define('PATH_BIN', PATH_APP . '/bin');

// Local
$app['locale'] = 'fr';
$app['session.default_locale'] = $app['locale'];
$app['translator.messages'] = array(
    'fr' => PATH_SRC . '/locales/fr.yml',
);

// Cache
$app['cache.path'] = PATH_BIN;

// Http cache
$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';

// Twig cache
$app['twig.options.cache'] = $app['cache.path'] . '/twig';

// Assetic
$app['assetic.enabled']              = true;
$app['assetic.path_to_cache']        = $app['cache.path'] . '/assetic' ;
$app['assetic.path_to_web']          = PATH_WEB . '/assets';
$app['assetic.input.path_to_assets'] = PATH_SRC . '/assets';

$app['assetic.input.path_to_css']       = $app['assetic.input.path_to_assets'] . '/less/style.less';
$app['assetic.output.path_to_css']      = 'css/styles.css';
$app['assetic.input.path_to_js']        = array(
    PATH_APP.'/vendor/twitter/bootstrap/js/*.js',
    $app['assetic.input.path_to_assets'] . '/js/script.js',
);
$app['assetic.output.path_to_js']       = 'js/scripts.js';

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_sqlite',
    'host'     => 'localhost',
    'dbname'   => 'todo',
    'user'     => 'root',
    'password' => '',
    'path'     => PATH_APP . '/app.db',
);

// User
$app['security.users'] = array('username' => array('ROLE_USER', 'password'));