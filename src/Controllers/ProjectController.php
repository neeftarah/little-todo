<?php
namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Models\Project;
use Models\Task;

class ProjectController implements ControllerProviderInterface
{
    public function connect(Application $app) {
        $projectController = $app['controllers_factory'];
        $projectController->post('/',    array($this, 'addAction')) ->bind('project_add');
        $projectController->put('/{id}', array($this, 'editAction'))->bind('project_edit');
        $projectController->get('/{id}', array($this, 'listAction'))->bind('project_list');
        return $projectController;
    }

    public function addAction(Application $app) {
        try {
            $name = $app["request"]->get('new_project');
            Project::addProject($app, array(
                'name' => $name,
            ));
        } catch (Exception $e) {
            return new Response('<h1>Insertion failed!</h1>', $e->getStatusCode());
        }

        return new Response('OK : ' . $name);
    }

    public function editAction(Application $app, $id, Request $request) {

    }

    public function listAction(Application $app, $id) {
        return $app['twig']->render('project.html.twig', array(
            'meta'            => array('title' => 'littleTodo'),
            'page'            => array('title' => 'littleTodo'),
            'current_project' => $id,
            'projects'        => Project::listprojects($app),
            'tasks'           => Task::listTasks($app, $id),
        ));
    }


    // TODO: Move into model
    protected function getProjects($db) {
        $query = "SELECT p.id, p.name, COUNT(t.id) AS tasks
                 FROM projects p
                 LEFT JOIN tasks t ON t.project_id = p.id
                 GROUP BY p.id
                 ORDER BY p.orderno";
        $st = $db->prepare($query);
        $st->execute();
        return $st->fetchAll();
    }
}
