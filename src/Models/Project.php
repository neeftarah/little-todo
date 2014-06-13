<?php

namespace Models;

use \Silex\Application;

class Project
{

    /**
     * Add a new project to the database.
     *
     * params $datas Array Project informations
     * return Integer number of affected row => 1 on success, 0 on failure
     */
    public static function addProject(Application $app, $datas) {
        return $app['db']->insert('user', $datas);
    }

    /**
     * Update an existing project in the database.
     *
     * params $id Integer Project's ID
     * params $datas Array Project new informations
     * return Integer number of affected row => 1 on success, 0 on failure
     */
    public static function editProject(Application $app, $id, $datas) {
        return $app['db']->update('user', $datas, $id);
    }

    /**
     * Delete a project from the database.
     *
     * params $id Integer Project's ID
     * return Integer number of affected row => 1 on success, 0 on failure
     */
    public static function deleteProject(Application $app, $id) {
        return $app['db']->delete('projects', array('id' => (int) $id));
    }

    public static function getProject($id) {
        $query = "SELECT *
                  FROM projects
                  WHERE id = :id";
        return $app['db']->fetchAssoc($query, array('id' => (int) $id));
    }

    public static function listProjects(Application $app) {
        $query = "SELECT p.id, p.name, COUNT(t.id) AS tasks
                  FROM projects p
                  LEFT JOIN tasks t ON t.project_id = p.id
                  GROUP BY p.id
                  ORDER BY p.orderno";
        return $app['db']->fetchAll($query);
    }
}