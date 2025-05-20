import 'package:flutter_mobile_app/services/api_client.dart';
import 'package:flutter_mobile_app/services/api_routes.dart';

class UserService {
  final ApiClient _apiClient = ApiClient();

  Future<dynamic> getUserProfile(int userId) async {
    return await _apiClient.get(ApiRoutes.getUserProfile(userId));
  }
}