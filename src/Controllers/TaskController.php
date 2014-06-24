<?php
namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Models\Task;

class TaskController implements ControllerProviderInterface
{
    public function connect(Application $app) {
        $taskController = $app['controllers_factory'];
        $taskController->post('/', array($this, 'addAction'))->bind('task_add');
        $taskController->put('/', array($this, 'editAction'))->bind('task_edit');
        $taskController->get('/', array($this, 'listAction'))->bind('task_list');
        return $taskController;
    }

    public function addAction(Application $app, Request $request) {
        $datas = array(
            'project_id' => (int) $request->get('current_project'),
            'title' => htmlentities($request->get('new_task')),
        );
        Task::addTask($app, $datas);

        return new Response('OK : ' . $task);
    }

    public function editAction(Application $app, Request $request) {
        $datas = array(
            'title' => htmlentities($request->get('task_title')),
        );
        Task::editTask($app, (int) $request->get('task_id'), $datas);

        return new Response('OK : ' . $task);
    }

    public function listAction(Application $app, $id, Request $request) {

    }
}
