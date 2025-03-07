import 'package:flutter_dotenv/flutter_dotenv.dart';

class ApiRoutes {
  static final String baseUrl = dotenv.env['API_BASE_URL'] ?? '';

  static String addUser = '$baseUrl/user/add';
  static String loginUser = '$baseUrl/user/login';
  static String addTask = '$baseUrl/tasks/add';
  static String connection = '$baseUrl/check';
  // $routes->get('/check', 'Home::connection');
  // static String viewTask = '$baseUrl/view/$num';
}


// $routes->post('users/add', 'UsersController::addUser');
// $routes->post('users/login', 'UsersController::login');

// // tasks route
// $routes->post('tasks/add', 'TasksController::addTask');
// $routes->get('tasks/view/(:num)', 'TasksController::viewTask/$1');
// $routes->delete('tasks/delete/(:num)', 'TasksController::deleteTask/$1');
// $routes->put('tasks/edit/(:num)', 'TasksController::editTask/$1');