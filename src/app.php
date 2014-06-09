<?php
// src/app.php
require_once PATH_APP . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
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
$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());
    $translator->addResource('yaml', PATH_SRC.'/locales/en.yml', 'en');
    $translator->addResource('yaml', PATH_SRC.'/locales/fr.yml', 'fr');
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
   'twig.path'    => PATH_SRC . '/views',
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
      ),
      'assetic.filters' => $app->protect(function($fm) use ($app) {
         $fm->set('lessphp', new Assetic\Filter\LessphpFilter());
      }),
      'assetic.assets' => $app->protect(function($am, $fm) use ($app) {
         $am->set('styles', new Assetic\Asset\AssetCache(
            new Assetic\Asset\GlobAsset(
               $app['assetic.input.path_to_css'],
               array($fm->get('lessphp'))
            ),
            new Assetic\Cache\FilesystemCache($app['assetic.path_to_cache'])
         ));
         $am->get('styles')->setTargetPath($app['assetic.output.path_to_css']);

         $am->set('scripts', new Assetic\Asset\AssetCache(
            new Assetic\Asset\GlobAsset(
               $app['assetic.input.path_to_js']
            ),
            new Assetic\Cache\FilesystemCache($app['assetic.path_to_cache'])
         ));
         $am->get('scripts')->setTargetPath($app['assetic.output.path_to_js']);
      })
   ));
}


// // Use PDO to access our sqlite db.
// $app['pdo'] = $app->share(function($app) {
//   return new \PDO('sqlite:' . $app['littleTodo.storage']);
// });

// // Class that will handle our list management.
// $app['littleTodo'] = $app->share(function($app) {
//   return new ToDoList($app['pdo']);
// });


// Home page route. TODO: Move business code in a controller class
$app->get('/', function () use($app) {
   return $app['twig']->render('index.html.twig', array(
      'meta'            => array('title' => 'littleTodo'),
      'page'            => array('title' => 'littleTodo'),
      'current_project' => 0,
      'projects'        => array(), // getProjects($app['pdo']),
      'tasks'           => array(),
   ));
})
->bind('homepage');

// Project page route. Tasks list. TODO: Move business code in a controller class
$app->get('/project/{id}', function($id) use ($app) {
   return $app['twig']->render('project.html.twig', array(
      'meta'            => array('title' => 'littleTodo'),
      'page'            => array('title' => 'littleTodo'),
      'current_project' => $id,
      'projects'        => getProjects($app['pdo']),
      'tasks'           => getTasks($app['pdo'], $id),
   ));
})
->bind('project');

// Project adding route. TODO: Move business code in a controller class
$app->post('/project/add', function (Request $request) use($app) {
   $project = $request->get('new_project');
   $db      = $app['pdo'];

   try {
      // Get order
      $st = $db->prepare("SELECT MAX(orderno) FROM projects");
      $st->execute();
      list($orderno) = $st->fetch();
      $orderno++;

      $st = $db->prepare("INSERT INTO projects (name, orderno) VALUES (:name, :orderno)");
      $st->bindValue(':name', $project);
      $st->bindValue(':orderno', $orderno);
      $st->execute();
   } catch (Exception $e) {
        return new Response('<h1>Insertion failed!</h1>', $e->getStatusCode());
   }
   return new Response('OK : ' . $project);
})
->bind('project_add');

// Task adding route. TODO: Move business code in a controller class
$app->post('/task/add', function (Request $request) use($app) {
   $task       = $request->get('new_task');
   $project_id = $request->get('current_project');
   $db         = $app['pdo'];
   try {
      $st = $db->prepare("INSERT INTO tasks (project_id, title) VALUES (:project_id, :title)");
      $st->bindValue(':project_id', $project_id);
      $st->bindValue(':title', $task);
      $st->execute();
   } catch (Exception $e) {
        return new Response('<h1>Insertion failed!</h1>', $e->getStatusCode());
   }
   return new Response('OK : ' . $task);
})
->bind('task_add');

// TODO: Move business code in a controller class
function getProjects($db) {
   $query = "SELECT p.id, p.name, COUNT(t.id) AS tasks
             FROM projects p
             LEFT JOIN tasks t ON t.project_id = p.id
             GROUP BY p.id
             ORDER BY p.orderno";
   $st = $db->prepare($query);
   $st->execute();
   return $st->fetchAll();
}

// TODO: Move business code in a controller class
function getTasks($db, $project_id, $finished = 0) {
   $query = "SELECT id, title, deadline, priority
             FROM tasks
             WHERE project_id = :project_id
             AND is_finished = :finished
             ORDER BY deadline, priority";
   $st = $db->prepare($query);
   $st->bindValue(':project_id', $project_id);
   $st->bindValue(':finished', $finished);
   $st->execute();
   return $st->fetchAll();
}

require PATH_SRC . '/routes.php';

return $app;