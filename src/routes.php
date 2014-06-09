<?php

$app->mount('/', new Controllers\IndexController());
$app->mount('/user', new Controllers\UserController());
$app->mount('/task', new Controllers\TaskController());
$app->mount('/project', new Controllers\ProjectController());
