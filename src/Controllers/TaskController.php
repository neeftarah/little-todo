<?php
namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class TaskController implements ControllerProviderInterface
{
    public function connect(Application $app) {
        $taskController = $app['controllers_factory'];
        $taskController->post('/', array($this, 'addAction'))->bind('task_add');
        $taskController->put('/', array($this, 'editAction'))->bind('task_edit');
        $taskController->get('/', array($this, 'listAction'))->bind('task_list');
        return $taskController;
    }

    public function addAction(Request $request) {
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
    }

    public function editAction($id, Request $request) {

    }

    public function listAction($id, Request $request) {

    }

    // TODO: Move into model
    protected function getTasks($db, $project_id, $finished = 0) {
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
}
