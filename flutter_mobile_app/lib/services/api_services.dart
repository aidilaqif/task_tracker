import 'api_services/connection_service.dart';
import 'api_services/auth_service.dart';
import 'api_services/task_service.dart';
import 'api_services/team_service.dart';
import 'api_services/user_service.dart';
import 'api_services/notification_service.dart';

class ApiService {
  // Initialize individual services
  final ConnectionService _connectionService = ConnectionService();
  final AuthService _authService = AuthService();
  final TaskService _taskService = TaskService();
  final TeamService _teamService = TeamService();
  final UserService _userService = UserService();
  final NotificationApiService _notificationService = NotificationApiService();

  // Connection check
  Future<dynamic> checkConnection() async {
    return await _connectionService.checkConnection();
  }

  // Auth methods
  Future<dynamic> loginUser(Map<String, dynamic> data) async {
    return await _authService.loginUser(data);
  }

  Future<dynamic> logoutUser() async {
    return await _authService.logoutUser();
  }

  // Task methods
  Future<dynamic> getUserTasks(
    int userId, {
    String? status,
    bool includeReassigned = false,
  }) async {
    return await _taskService.getUserTasks(
      userId,
      status: status,
      includeReassigned: includeReassigned,
    );
  }

  Future<dynamic> viewTask(int taskId, {int? userId}) async {
    return await _taskService.viewTask(taskId, userId: userId);
  }

  Future<dynamic> updateTaskStatus(
    int taskId,
    String status, [
    int? userId,
  ]) async {
    return await _taskService.updateTaskStatus(taskId, status, userId);
  }

  Future<dynamic> updateTaskProgress(
    int taskId,
    int progress, [
    int? userId,
  ]) async {
    return await _taskService.updateTaskProgress(taskId, progress, userId);
  }

  // Team methods
  Future<dynamic> getTeamInfo(int teamId) async {
    return await _teamService.getTeamInfo(teamId);
  }

  Future<dynamic> getTeamMembers(int teamId) async {
    return await _teamService.getTeamMembers(teamId);
  }

  // User methods
  Future<dynamic> getUserProfile(int userId) async {
    return await _userService.getUserProfile(userId);
  }

  // Notification methods
  Future<dynamic> getUserNotifications(int userId, {bool? isRead}) async {
    return await _notificationService.getUserNotifications(
      userId,
      isRead: isRead,
    );
  }

  Future<dynamic> markNotificationAsRead(int notificationId) async {
    return await _notificationService.markNotificationAsRead(notificationId);
  }
}
