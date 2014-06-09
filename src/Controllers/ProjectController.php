<?php
namespace Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ProjectController implements ControllerProviderInterface
{
    public function connect(Application $app) {
        $projectController = $app['controllers_factory'];
        $projectController->post('/',    array($this, 'addAction')) ->bind('project_add');
        $projectController->put('/{id}', array($this, 'editAction'))->bind('project_edit');
        $projectController->get('/{id}', array($this, 'listAction'))->bind('project_list');
        return $projectController;
    }

    public function addAction($id) {
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
    }

    public function editAction($id, Request $request) {

    }

    public function listAction(Request $request) {
        return $app['twig']->render('project.html.twig', array(
            'meta'            => array('title' => 'littleTodo'),
            'page'            => array('title' => 'littleTodo'),
            'current_project' => $id,
            'projects'        => getProjects($app['pdo']),
            'tasks'           => getTasks($app['pdo'], $id),
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
