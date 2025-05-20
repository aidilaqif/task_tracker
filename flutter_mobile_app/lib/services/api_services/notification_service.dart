import 'package:flutter_mobile_app/services/api_client.dart';
import 'package:flutter_mobile_app/services/api_routes.dart';

class NotificationApiService {
  final ApiClient _apiClient = ApiClient();

  Future<dynamic> getUserNotifications(int userId, {bool? isRead}) async {
    String url = ApiRoutes.getNotifications(userId);
    if (isRead != null) {
      url += '?is_read=${isRead ? 1 : 0}';
    }

    return await _apiClient.get(url);
  }

  Future<dynamic> markNotificationAsRead(int notificationId) async {
    return await _apiClient.put(ApiRoutes.markNotificationAsRead(notificationId));
  }
}