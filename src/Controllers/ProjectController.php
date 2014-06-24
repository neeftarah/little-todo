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

    public function addAction(Application $app, $id) {
        $datas['name'] = $request->get('new_project');
        Project::addProject($app, $datas)

        return new Response('OK : ' . $project);
    }

    public function editAction(Application $app, $id, Request $request) {
        $datas['name'] = $request->get('project_name');
        $project_id    = $request->get('project_id');
        Project::editProject($app, $datas, $project_id);

        return new Response('OK : ' . $project);
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
}
