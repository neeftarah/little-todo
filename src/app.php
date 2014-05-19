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

// If in developpment
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

// Register Monolog provider
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

// Exception management
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



$app->get('/', function () use($app) {
   return $app['twig']->render('index.html.twig', array(
      'meta'            => array('title' => 'littleTodo'),
      'page'            => array('title' => 'littleTodo'),
      'current_project' => 1,
      'projects'        => array(
                              1 => array('id' => 1, 'name' => 'Project 1', 'tasks' => '42'),
                              2 => array('id' => 2, 'name' => 'Project 2', 'tasks' => '25'),
                              3 => array('id' => 3, 'name' => 'Project 3', 'tasks' => '7'),
                           ),
   ));
});

$app->post('/project/add', function (Request $request) use($app) {
   $project = $request->get('new_project');
   $db = $app['pdo'];

   $st = $this->db->prepare("INSERT INTO projects (name) VALUES (:name)");
   $st->bindValue(':name', $project);
   $st->execute();

   return $app->redirect('/');
});

return $app;