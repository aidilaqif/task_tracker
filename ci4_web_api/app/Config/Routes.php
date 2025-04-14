<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Web View - Default Route for Sidebar
$routes->get('/', 'WebUIController::dashboard');

// Web View - Routes for Sidebar
$routes->get('/dashboard', 'WebUIController::dashboard');
$routes->get('/team', 'WebUIController::team');
$routes->get('/task', 'WebUIController::task');
$routes->get('/teams/(:num)/members', 'WebUIController::teamDetail/$1');

$routes->get('/check', 'Home::connection');

// users route
$routes->post('users/add', 'UsersController::addUser');
$routes->post('users/login', 'UsersController::login');
$routes->post('users/logout', 'UsersController::logout');

// tasks route
$routes->get('tasks', 'TasksController::getAllTasks');
$routes->post('tasks/add', 'TasksController::addTask');
$routes->post('tasks/assign', 'TasksController::assignTask');
$routes->get('tasks/view/(:num)', 'TasksController::viewTask/$1');
$routes->delete('tasks/delete/(:num)', 'TasksController::deleteTask/$1');
$routes->put('tasks/edit/(:num)', 'TasksController::editTask/$1');
$routes->get('tasks/user/(:num)', 'TasksController::getUserTasks/$1');
$routes->put('tasks/status/(:num)', 'TasksController::updateTaskStatus/$1');
$routes->get('tasks/priorities', 'TasksController::getPriorityLevels');
$routes->put('tasks/priority/(:num)', 'TasksController::updateTaskPriority/$1');

// progress tracking routes
$routes->put('tasks/progress/(:num)', 'TasksController::updateTaskProgress/$1');
$routes->get('tasks/stats', 'TasksController::getTaskCompletionStats');

// team route
$routes->get('teams','TeamController::getAllTeams');
$routes->get('teams/with-count','TeamController::getTeamsWithMemberCount');
$routes->get('teams/(:num)','TeamController::getTeam/$1');
$routes->get('teams/(:num)/members', 'TeamController::getTeamMembers/$1');
$routes->post('teams', 'TeamController::createTeam');
$routes->post('teams/members','TeamController::addUserToTeam');
$routes->delete('teams/members/(:num)', 'TeamController::removeUserFromTeam/$1');
$routes->get('teams/(:num)/metrics','TeamController::getTeamPerformanceMetrics/$1');

// users-team related route
$routes->get('users/team/(:num)', 'UsersController::getUsersByTeam/$1');
$routes->post('users/team','UsersController::updateUserTeam');
$routes->delete('users/team/(:num)', 'UsersController::removeUserFromTeam/$1');

// notification routes
$routes->post('notifications/task', 'NotificationsController::sendTaskNotification');
$routes->get('notifications/user/(:num)', 'NotificationsController::getUserNotifications/$1');
$routes->put('notifications/read/(:num)', 'NotificationsController::markAsRead/$1');