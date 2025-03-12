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
      // Ensure user_id is sent as a number
      if (data.containsKey('user_id') && data['user_id'] is String) {
        data['user_id'] = int.tryParse(data['user_id']) ?? 0;
      }

      // print('Sending task data: ${jsonEncode(data)}');

      final response = await http.post(
        Uri.parse(ApiRoutes.addTask),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(data),
      );
      // print('Response status code: ${response.statusCode}');
      // print('Response body: ${response.body}');

      final responseData = jsonEncode(response.body);

      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Get All Tasks for a User
  Future<dynamic> getAllTasks(int userId) async {
    try {
      // Ensure userId is a valid integer
      if (userId <= 0) {
        // print('Invalid user ID: $userId');
        return {'status': false, 'msg': 'Invalid user ID', 'data': []};
      }

      final response = await http.get(
        Uri.parse(ApiRoutes.getAllTasks(userId)),
        headers: {'Content-Type': 'application/json'},
      );

      // print('Response status code: ${response.statusCode}');
      // print('Response body: ${response.body}');

      if (response.statusCode != 200) {
        // print('Non-200 status code: ${response.statusCode}');
        return {
          'status': false,
          'msg': 'Server error: ${response.statusCode}',
          'data': [],
        };
      }

      final responseData = jsonDecode(response.body);

      // Make sure we return an empty list instead of null for data
      if (responseData['status'] == true && responseData['data'] == null) {
        responseData['data'] = [];
      }
      return responseData;
    } catch (e) {
      // print('Error fetching tasks: $e');
      return {
        'status': false,
        'msg': 'Network error: ${e.toString()}',
        'data': [],
      };
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
  Future<dynamic> deleteTask(int taskId) async {
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
