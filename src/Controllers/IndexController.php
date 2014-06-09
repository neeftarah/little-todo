<?php
namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;

class IndexController implements ControllerProviderInterface
{
    public function connect(Application $app) {
        $indexController = $app['controllers_factory'];
        $indexController->get('/', array($this, 'index'))->bind('homepage');
        return $indexController;
    }

    public function index(Application $app) {
        return $app['twig']->render('index.html.twig', array(
            'meta'            => array('title' => 'littleTodo'),
            'page'            => array('title' => 'littleTodo'),
            'current_project' => 0,
            'projects'        => array(), // getProjects($app['pdo']),
            'tasks'           => array(),
        ));
    }
}
