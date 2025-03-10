import 'dart:convert';
import 'package:http/http.dart' as http;
import 'api_routes.dart';

class ApiService {
  // Check Connection with Database
  Future<dynamic> checkConnection() async {
    try {
      final response = await http.get(Uri.parse(ApiRoutes.connection));

      if (response.statusCode == 200) {
        return ('successful');
      } else {
        throw Exception('Failed to connect to server: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Failed to load data: $e');
    }
  }

  // Login User
  Future<dynamic> loginUser(Map<String, dynamic> data) async {
    try {
      final response = await http.post(
        Uri.parse(ApiRoutes.loginUser),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(data),
      );

      final responseData = jsonDecode(response.body);

      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Register User
  Future<dynamic> registerUser(Map<String, dynamic> data) async {
    try {
      final response = await http.post(
        Uri.parse(ApiRoutes.addUser),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(data),
      );

      final responseData = jsonDecode(response.body);

      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Add Tasks
  Future<dynamic> addTask(Map<String, dynamic> data) async {
    try {
      final response = await http.post(
        Uri.parse(ApiRoutes.addTask),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(data),
      );

      final responseData = jsonEncode(response.body);

      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Get All Tasks for a User
  Future<dynamic> getAllTasks(int userId) async {
    try {
      final response = await http.get(
        Uri.parse(ApiRoutes.getAllTasks(userId)),
        headers: {'Content-Type': 'application/json'},
      );

      final responseData = jsonDecode(response.body);

      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // View Single Task
  Future<dynamic> viewTask(int taskId) async {
    try {
      final response = await http.get(
        Uri.parse(ApiRoutes.viewTask(taskId)),
        headers: {'Content-Type': 'application/json'},
      );

      final responseData = jsonDecode(response.body);

      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Delete Task
  Future<dynamic> deleteTasks(int taskId) async {
    try {
      final response = await http.delete(
        Uri.parse(ApiRoutes.deleteTask(taskId)),
        headers: {'Content-Type': 'applicaiton/json'},
      );

      final responseData = jsonDecode(response.body);

      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Edit Task
  Future<dynamic> editTask(int taskId, Map<String, dynamic> data) async {
    try {
      final response = await http.put(
        Uri.parse(ApiRoutes.editTask(taskId)),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(data),
      );

      final responseData = jsonDecode(response.body);

      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }
}
