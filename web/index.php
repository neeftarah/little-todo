<?php
// web/index.php
define('APP_DIR', dirname(__DIR__));
require_once APP_DIR . '/vendor/autoload.php';

use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

// If in developpment
$app['debug']              = true;
$app['cache.path']         = APP_DIR . '/bin';
$app['littleTodo.storage'] = APP_DIR . '/app.db';

// //register twig provider
$app->register(new TwigServiceProvider(), array(
   'twig.path'    => APP_DIR . '/src/views',
   'twig.options' => array('cache' => (!empty($app['cache.path']) && !$app['debug'])
                                       ? $app['cache.path'] . '/twig'
                                       : FALSE),
));



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



$app->run();