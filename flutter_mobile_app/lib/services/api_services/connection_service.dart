import 'package:http/http.dart' as http;
import 'package:flutter_mobile_app/services/api_routes.dart';

class ConnectionService {
  Future<String> checkConnection() async {
    try {
      await http.get(
        Uri.parse(ApiRoutes.baseUrl),
        headers: {'Content-Type': 'application/json'},
      );
      return 'successful';
    } catch (e) {
      throw Exception('Failed to connect to server: $e');
    }
  }
}
