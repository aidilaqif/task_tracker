import 'dart:convert';
import 'package:http/http.dart' as http;
import 'api_routes.dart';

class ApiService {
  // Connection check
  Future<dynamic> checkConnection() async {
    try {
      await http.get(
        Uri.parse(ApiRoutes.baseUrl), // Just check if base URL is reachable
        headers: {'Content-Type': 'application/json'},
      );
      return('successful');
    } catch (e) {
      throw Exception('Failed to connect to server: $e');
    }
  }

  // 1. Authentication
  // Login user
  Future<dynamic> loginUser(Map<String, dynamic> data) async {
    try {
      final response = await http.post(
        Uri.parse(ApiRoutes.login),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(data),
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch(e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Logout user
  Future<dynamic> logoutUser() async {
    try {
      final response = await http.post(
        Uri.parse(ApiRoutes.logout),
        headers: {'Content-Type': 'application/json'},
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // 2. Task Management
  // Get Tasks for a user (with optional status filter)
  Future<dynamic> getUserTasks(int userId, {String? status, bool includeReassigned = false}) async {
    try {
      String url = status != null
        ? ApiRoutes.getUserTasksByStatus(userId, status)
        : ApiRoutes.getUserTasks(userId);

      if (includeReassigned) {
        url += url.contains('?') ? '&include_reassigned=1' : '?include_reassigned=1';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
      );

      if (response.statusCode != 200) {
        return {
          'status': false,
          'msg': 'Server error: ${response.statusCode}',
          'data': [],
        };
      }

      final responseData = jsonDecode(response.body);

      if (responseData['status'] == true && responseData['data'] == null) {
        responseData['data'] = [];
      }

      return responseData;
    } catch (e) {
      return {
        'status': false,
        'msg': 'Network error: ${e.toString()}',
        'data': [],
      };
    }
  }

  // View single task
  Future<dynamic> viewTask(int taskId, {int? userId}) async {
    try {
      String url = ApiRoutes.viewTask(taskId);

      if (userId != null) {
        url += '?user_id=$userId';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'}
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Update Task Status (for requesting extension)
  Future<dynamic> updateTaskStatus(int taskId, String status, [int? userId]) async {
    try {
      String url = ApiRoutes.updateTaskStatus(taskId);

      if (userId != null) {
        url += '?user_id=$userId';
      }

      final response = await http.put(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'status': status}),
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Update Task Progress
  Future<dynamic> updateTaskProgress(int taskId, int progress, [int? userId]) async {
    try {
      String url = ApiRoutes.updateTaskProgress(taskId);

      if (userId != null) {
        url += '?user_id=$userId';
      }
      final response = await http.put(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'progress': progress}),
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // 3. Team Information
  // Get Basic Team Info
  Future<dynamic> getTeamInfo(int teamId) async {
    try {
      final response = await http.get(
        Uri.parse(ApiRoutes.getTeamBasicInfo(teamId)),
        headers: {'Content-Type': 'application/json'},
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Get Team Members
  Future<dynamic> getTeamMembers(int teamId) async {
    try {
      final response = await http.get(
        Uri.parse(ApiRoutes.getTeamMembers(teamId)),
        headers: {'Content-Type': 'application/json'},
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // 4. User Profile
  // Get User Profile
  Future<dynamic> getUserProfile(int userId) async {
    try {
      final response = await http.get(
        Uri.parse(ApiRoutes.getUserProfile(userId)),
        headers: {'Content-Type': 'application/json'},
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // 5. Notifications
  // Get User Notifications
  Future<dynamic> getUserNotifications(int userId, {bool? isRead}) async {
    try {
      String url = ApiRoutes.getNotifications(userId);
      if (isRead != null) {
        url += '?is_read=${isRead ? 1 : 0}';
      }

      final response = await http.get(
        Uri.parse(url),
        headers: {'Content-Type': 'application/json'},
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  // Mark Notification as Read
  Future<dynamic> markNotificationAsRead(int notificationId) async {
    try {
      final response = await http.put(
        Uri.parse(ApiRoutes.markNotificationAsRead(notificationId)),
        headers: {'Content-Type': 'application/json'},
      );

      final responseData = jsonDecode(response.body);
      return responseData;
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }
}