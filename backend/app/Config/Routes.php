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

// tasks route
$routes->post('tasks/add', 'TasksController::addTask');
$routes->get('tasks/view/(:num)', 'TasksController::viewTask/$1');
$routes->delete('tasks/delete/(:num)', 'TasksController::deleteTask/$1');
$routes->put('tasks/edit/(:num)', 'TasksController::editTask/$1');