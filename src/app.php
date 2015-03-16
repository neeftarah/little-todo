<?php
// src/app.php
require_once PATH_APP . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

$app->register(new Silex\Provider\HttpCacheServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider());

// Register security provider
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^/',
            'form'    => array(
                'login_path'         => '/login',
                'username_parameter' => 'form[username]',
                'password_parameter' => 'form[password]',
            ),
            'logout'    => true,
            'anonymous' => true,
            'users'     => $app['security.users'],
        ),
    ),
));

$app['security.encoder.digest'] = $app->share(function ($app) {
    return new PlaintextPasswordEncoder();
});

// Register translation provider
$app->register(new Silex\Provider\TranslationServiceProvider());
$app['translator'] = $app->share($app->extend('translator', function(Silex\Translator $translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', PATH_SRC.'/Locales/en.yml', 'en');
    $translator->addResource('yaml', PATH_SRC.'/Locales/fr.yml', 'fr');
    return $translator;
}));

// Register Monolog provider.
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => PATH_APP . '/log/app.log',
    'monolog.name'    => 'littleTodo',
    'monolog.level'   => 300,  // => Logger::WARNING
));

// Register Twig provider
$app->register(new Silex\Provider\TwigServiceProvider(), array(
   'twig.path'    => PATH_SRC . '/Views',
   'twig.options' => array(
      'cache'            => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
      'strict_variables' => true
   ),
));

// Register profiler if in dev mod
if ($app['debug'] && isset($app['cache.path'])) {
    $app->register(new Silex\Provider\ServiceControllerServiceProvider());
    $app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => $app['cache.path'].'/profiler',
    ));
}

// Exception management.
$app->error(function(\Exception $e) use ($app) {
   // 404 - Page not found
   if ($e instanceof NotFoundHttpException) {
      return new Response($app['twig']->render('404.html.twig', array(
         'meta' => array('title' => 'Page not found - Error 404'),
         'page' => array(
            'title'   => 'Page not found - Error 404',
            'content' => 'The page you are searching for can\'t be find.',
         ),
      )), 404);

   // Other Error - 500
   } elseif ($e instanceof HttpException) {
      return new Response($app['twig']->render('500.html.twig', array(
         'meta' => array('title' => 'Internal error - Error 500'),
         'page' => array(
            'title'   => 'Internal error - Error 500',
            'content' => 'An internal error occur during page generation.',
         ),
      )), 500);
   }
});

// Define assets
if (isset($app['assetic.enabled']) && $app['assetic.enabled']) {
    $app->register(new SilexAssetic\AsseticServiceProvider(), array(
        'assetic.options' => array(
            'debug'            => $app['debug'],
            'auto_dump_assets' => $app['debug'],
        )
    ));

    $app['assetic.filter_manager'] = $app->share(
        $app->extend('assetic.filter_manager', function (Assetic\FilterManager $fm, $app) {
            $fm->set('lessphp', new Assetic\Filter\LessphpFilter());

            return $fm;
        })
    );

    $app['assetic.asset_manager'] = $app->share(
        $app->extend('assetic.asset_manager', function (Assetic\AssetManager $am, $app) {
            $am->set('styles', new Assetic\Asset\AssetCache(
                new Assetic\Asset\GlobAsset(
                    $app['assetic.input.path_to_css'],
                    array($app['assetic.filter_manager']->get('lessphp'))
                ),
                new Assetic\Cache\FilesystemCache($app['assetic.path_to_cache'])
            ));
            $am->get('styles')->setTargetPath($app['assetic.output.path_to_css']);

            $am->set('scripts', new Assetic\Asset\AssetCache(
                new Assetic\Asset\GlobAsset($app['assetic.input.path_to_js']),
                new Assetic\Cache\FilesystemCache($app['assetic.path_to_cache'])
            ));
            $am->get('scripts')->setTargetPath($app['assetic.output.path_to_js']);

            return $am;
        })
    );
}

require PATH_SRC . '/routes.php';

return $app;
