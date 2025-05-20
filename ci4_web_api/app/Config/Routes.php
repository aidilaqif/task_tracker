<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Authentication Routes
$routes->get('/login', 'AuthController::login');
$routes->post('/auth/set-session', 'AuthController::setSession');
$routes->get('/logout', 'AuthController::logout');

// Web View - Default Route for Sidebar (protected by auth filter)
$routes->get('/', 'WebUIController::dashboard', ['filter' => 'auth']);

// Web View - Routes for Sidebar (protected by auth filter)
$routes->get('/dashboard', 'WebUIController::dashboard', ['filter' => 'auth']);
$routes->get('/team', 'WebUIController::team', ['filter' => 'auth']);
$routes->get('/task', 'WebUIController::task', ['filter' => 'auth']);
$routes->get('/notifications', 'WebUIController::notifications', ['filter' => 'auth']);
$routes->get('/team_detail', 'WebUIController::teamDetail', ['filter' => 'auth']);
$routes->get('/task_detail', 'WebUIController::taskDetail', ['filter' => 'auth']);
$routes->get('/user', 'WebUIController::user', ['filter' => 'auth']);

// users route
$routes->get('users', 'UsersController::getAllUsers');
$routes->post('users/add', 'UsersController::addUser');
$routes->post('users/login', 'UsersController::login');
$routes->post('users/logout', 'UsersController::logout');
$routes->get('users/(:num)', 'UsersController::getUser/$1');  // Get a specific user by ID
$routes->put('users/(:num)', 'UsersController::updateUser/$1');  // Update a user's details
$routes->delete('users/(:num)', 'UsersController::deleteUser/$1');

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

// dashboard related route
$routes->get('tasks/dashboard-metrics', 'TasksController::getDashboardMetrics');

// team route
$routes->get('teams','TeamController::getAllTeams');
$routes->get('teams/with-count','TeamController::getTeamsWithMemberCount');
$routes->get('teams/(:num)','TeamController::getTeam/$1');
$routes->get('teams/(:num)/members', 'TeamController::getTeamMembers/$1');
$routes->post('teams', 'TeamController::createTeam');
$routes->post('teams/members','TeamController::addUserToTeam');
$routes->delete('teams/members/(:num)', 'TeamController::removeUserFromTeam/$1');
$routes->get('teams/(:num)/metrics','TeamController::getTeamPerformanceMetrics/$1');
$routes->put('teams/(:num)', 'TeamController::updateTeam/$1');

// users-team related route
$routes->get('users/team/(:num)', 'UsersController::getUsersByTeam/$1');
$routes->get('users/no-team', 'UsersController::getUsersWithoutTeam');
$routes->post('users/team','UsersController::updateUserTeam');
$routes->delete('users/team/(:num)', 'UsersController::removeUserFromTeam/$1');

// notification routes
$routes->post('notifications/task', 'NotificationsController::sendTaskNotification');
$routes->get('notifications/user/(:num)', 'NotificationsController::getUserNotifications/$1');
$routes->put('notifications/read/(:num)', 'NotificationsController::markAsRead/$1');
$routes->post('notifications/task-assignment', 'TasksController::sendTaskAssignmentNotification');

// Admin Notification Routes
$routes->get('admin/notifications', 'NotificationsController::getAdminNotifications', ['filter' => 'auth']);
$routes->post('admin/notifications/mark-read/(:num)', 'NotificationsController::markAsRead/$1', ['filter' => 'auth']);
$routes->post('admin/notifications/mark-all-read', 'NotificationsController::markAllAsRead', ['filter' => 'auth']);
$routes->get('admin/notifications/unread-count', 'NotificationsController::getUnreadCount', ['filter' => 'auth']);