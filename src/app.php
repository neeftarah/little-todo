<?php
// src/app.php
if(!defined('APP_DIR'))
   define('APP_DIR', dirname(__DIR__));

require_once APP_DIR . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app = new Silex\Application();

// Application configuration. TODO: Move to a config file
$app['debug']              = true;
$app['cache.path']         = APP_DIR . '/bin';
$app['littleTodo.storage'] = APP_DIR . '/app.db';

if(!is_dir($app['cache.path'])) {
	mkdir($app['cache.path']);
}

// Register Twig provider
$app->register(new Silex\Provider\TwigServiceProvider(), array(
   'twig.path'    => APP_DIR . '/src/views',
   'twig.options' => array('cache' => (!empty($app['cache.path']) && !$app['debug'])
                                       ? $app['cache.path'] . '/twig'
                                       : FALSE),
));

// Register Monolog provider. TODO: Move  tests in an installation file
if(!file_exists(APP_DIR . '/log/app.log')) {
	$file = fopen(APP_DIR . '/log/app.log', 'w');
	if($file) {
		fclose($file);
	} elseif($app['debug']) {
      throw new Exception("Log files can\'t be created!");
	}
} elseif(!is_writable(APP_DIR . '/log/app.log') && ($app['debug'])) {
   throw new Exception("Log file is not writable!");
}

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => APP_DIR . '/log/app.log',
    'monolog.name'    => 'littleTodo',
    'monolog.level'   => 300	// => Logger::WARNING
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Exception management.
$app->error(function(\Exception $e) use ($app) {
    if ($e instanceof NotFoundHttpException) {
        $content = vsprintf('<h1>%d - %s (%s)</h1>', array(
           $e->getStatusCode(),
           Response::$statusTexts[$e->getStatusCode()],
           $app['request']->getRequestUri()
        ));
        return new Response($content, $e->getStatusCode());
    }

    if ($e instanceof HttpException) {
        return new Response('<h1>You should go eat some cookies while we\'re fixing this feature!</h1>', $e->getStatusCode());
    }
});


// Use PDO to access our sqlite db.
$app['pdo'] = $app->share(function($app) {
  return new \PDO('sqlite:' . $app['littleTodo.storage']);
});

// Class that will handle our list management.
$app['littleTodo'] = $app->share(function($app) {
  return new ToDoList($app['pdo']);
});


// Home page route. TODO: Move business code in a controller class
$app->get('/', function () use($app) {
   return $app['twig']->render('index.html.twig', array(
      'meta'            => array('title' => 'littleTodo'),
      'page'            => array('title' => 'littleTodo'),
      'current_project' => 0,
      'projects'        => getProjects($app['pdo']),
      'tasks'           => array(),
   ));
})
->bind('homepage');

// Project page route. Tasks list. TODO: Move business code in a controller class
$app->get('/project/{id}', function($id) use ($app) {
   return $app['twig']->render('index.html.twig', array(
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

return $app;