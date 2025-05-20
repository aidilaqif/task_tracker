// services/auth_service.dart
import 'package:flutter_mobile_app/services/api_client.dart';
import 'package:flutter_mobile_app/services/api_routes.dart';

class AuthService {
  final ApiClient _apiClient = ApiClient();

  Future<dynamic> loginUser(Map<String, dynamic> data) async {
    return await _apiClient.post(ApiRoutes.login, data);
  }

  Future<dynamic> logoutUser() async {
    return await _apiClient.post(ApiRoutes.logout);
  }
}
