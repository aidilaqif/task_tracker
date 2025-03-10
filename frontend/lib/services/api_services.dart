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
      return {
        'status': false,
        'msg': 'Network error: ${e.toString()}',
      };
    }
  }
  // Register User
  Future<dynamic> registerUser(Map<String, dynamic> data) async {
    try{
      final response = await http.post(
        Uri.parse(ApiRoutes.addUser),
        headers:  {'Content-Type': 'application/json'},
        body: jsonEncode(data),
      );

      final responseData = jsonDecode(response.body);

      return responseData;
    } catch (e) {
      return {
        'status': false,
        'msg' : 'Network error: ${e.toString()}',
      };
    }
  }
  // Add Tasks

  // View Tasks
  Future<dynamic> viewTasks(int taskId) async {
    try {
      final response = await http.get(
        Uri.parse(ApiRoutes.viewTask(taskId)),
        headers: {'Content-Type': 'application/json'},
      );

      final responseData = jsonDecode(response.body);

      return responseData;
    } catch (e) {
      return {
        'status': false,
        'msg': 'Network error: ${e.toString()}',
      };
    }
  }
}
