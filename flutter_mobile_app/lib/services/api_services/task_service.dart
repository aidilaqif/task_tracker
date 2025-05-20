// services/task_service.dart
import 'package:flutter_mobile_app/services/api_client.dart';
import 'package:flutter_mobile_app/services/api_routes.dart';

class TaskService {
  final ApiClient _apiClient = ApiClient();

  Future<dynamic> getUserTasks(
    int userId, {
    String? status,
    bool includeReassigned = false,
  }) async {
    try {
      String url =
          status != null
              ? ApiRoutes.getUserTasksByStatus(userId, status)
              : ApiRoutes.getUserTasks(userId);

      if (includeReassigned) {
        url +=
            url.contains('?')
                ? '&include_reassigned=1'
                : '?include_reassigned=1';
      }

      final response = await _apiClient.get(url);

      if (response['status'] == true && response['data'] == null) {
        response['data'] = [];
      }

      return response;
    } catch (e) {
      return {
        'status': false,
        'msg': 'Network error: ${e.toString()}',
        'data': [],
      };
    }
  }

  Future<dynamic> viewTask(int taskId, {int? userId}) async {
    try {
      String url = ApiRoutes.viewTask(taskId);

      if (userId != null) {
        url += '?user_id=$userId';
      }

      return await _apiClient.get(url);
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  Future<dynamic> updateTaskStatus(
    int taskId,
    String status, [
    int? userId,
  ]) async {
    try {
      String url = ApiRoutes.updateTaskStatus(taskId);

      if (userId != null) {
        url += '?user_id=$userId';
      }

      return await _apiClient.put(url, {'status': status});
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  Future<dynamic> updateTaskProgress(
    int taskId,
    int progress, [
    int? userId,
  ]) async {
    try {
      String url = ApiRoutes.updateTaskProgress(taskId);

      if (userId != null) {
        url += '?user_id=$userId';
      }

      return await _apiClient.put(url, {'progress': progress});
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }
}
