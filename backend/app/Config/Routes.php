<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/check', 'Home::connection');

// users route
$routes->post('users/add', 'UsersController::addUser');
$routes->post('users/login', 'UsersController::login');
$routes->post('users/logout', 'UsersController::logout');

// tasks route
$routes->post('tasks/add', 'TasksController::addTask');
$routes->post('tasks/assign', 'TasksController::assignTask');
$routes->get('tasks/view/(:num)', 'TasksController::viewTask/$1');
$routes->delete('tasks/delete/(:num)', 'TasksController::deleteTask/$1');
$routes->put('tasks/edit/(:num)', 'TasksController::editTask/$1');
$routes->get('tasks/user/(:num)', 'TasksController::getUserTasks/$1');
$routes->put('tasks/status/(:num)', 'TasksController::updateTaskStatus/$1');

// team route
$routes->get('teams','TeamController::getAllTeams');
$routes->get('teams/with-count','TeamController::getTeamsWithMemberCount');
$routes->get('teams/(:num)','TeamController::getTeam/$1');
$routes->get('teams/(:num)/members', 'TeamController::getTeamMembers/$1');
$routes->post('teams', 'TeamController::createTeam');
$routes->post('teams/members','TeamController::addUserToTeam');
$routes->delete('teams/members/(:num)', 'TeamController::removeUserFromTeam/$1');

// users-team related route
$routes->get('users/team/(:num)', 'UsersController::getUsersByTeam/$1');
$routes->post('users/team','UsersController::updateUserTeam');
$routes->delete('users/team/(:num)', 'UsersController::removeUserFromTeam/$1');