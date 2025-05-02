// import 'package:flutter_dotenv/flutter_dotenv.dart';

// class ApiRoutes {
//   static final String baseUrl = dotenv.env['API_BASE_URL'] ?? '';

//   // Authentication routes
//   static String login = '$baseUrl/users/login';
//   static String logout = '$baseUrl/users/logout';

//   // Connection check
//   static String connection = '$baseUrl/check';

//   // Task routes for employees
//   static String viewTask(int taskId) => '$baseUrl/tasks/view/$taskId';
//   static String getUserTasks(int userId) => '$baseUrl/tasks/user/$userId';
//   static String updateTaskStatus(int taskId) => '$baseUrl/tasks/status/$taskId';
//   static String updateTaskProgress(int taskId) => '$baseUrl/tasks/progress/$taskId';

//   // Filter options for tasks (ongoing, completed, etc)
//   static String getUserTasksByStatus(int userId, String status) => '$baseUrl/tasks/user/$userId?status=$status';

//   // Team information (limited details for team members)
//   static String getTeamBasicInfo(int teamId) => '$baseUrl/teams/$teamId';
//   static String getTeamMembers(int teamId) => '$baseUrl/teams/$teamId/members';

//   static String addUser = '$baseUrl/users/add';
//   static String loginUser = '$baseUrl/users/login';
//   static String logoutUser = '$baseUrl/users/loout';
  
// }


