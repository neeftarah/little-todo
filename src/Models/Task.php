<?php

namespace Models;

use Silex\Application;

class Task
{

    /**
     * Add a new task to the database.
     *
     * params $datas Array Task informations
     * return Integer number of affected row => 1 on success, 0 on failure
     */
    public static function addTask(Application $app, $datas) {
        $query = "SELECT MAX(orderno)
                  FROM tasks
                  WHERE project_id = :project_id";
        list($orderno) = $app['db']->fetch($query, array('project_id' => $datas['project_id']));
        $datas['orderno'] = $orderno + 1;

        return $app['db']->insert('tasks', $datas);
    }

    /**
     * Update an existing task in the database.
     *
     * params $id Integer Task's ID
     * params $datas Array Task new informations
     * return Integer number of affected row => 1 on success, 0 on failure
     */
    public static function editTask(Application $app, $id, $datas) {
        return $app['db']->update('tasks', $datas, $id);
    }

    /**
     * Delete a task from the database.
     *
     * params $id Integer Task's ID
     * return Integer number of affected row => 1 on success, 0 on failure
     */
    public static function deleteTask(Application $app, $id) {
        return $app['db']->delete('tasks', array('id' => (int) $id));
    }

    public static function getTask($id) {
        $query = "SELECT *
                  FROM tasks
                  WHERE id = :id";
        return $app['db']->fetchAssoc($query, array('id' => (int) $id));
    }

    public static function listTasks(Application $app, $project_id, $finished = 0) {
        $query = "SELECT id, title, deadline, priority
                  FROM tasks
                  WHERE project_id = :project_id
                  AND is_finished = :finished
                  ORDER BY deadline, priority";
        return $app['db']->fetchAll($query, array(
            'project_id'  => (int) $project_id,
            'finished' => (int) $finished));
    }
}
