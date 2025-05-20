import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiClient {
  Map<String, String> get _headers => {'Content-Type': 'application/json'};

  Future<Map<String, dynamic>> get(String url) async {
    try {
      final response = await http.get(Uri.parse(url), headers: _headers);

      return _processResponse(response);
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  Future<Map<String, dynamic>> post(
    String url, [
    Map<String, dynamic>? data,
  ]) async {
    try {
      final response = await http.post(
        Uri.parse(url),
        headers: _headers,
        body: data != null ? jsonEncode(data) : null,
      );

      return _processResponse(response);
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  Future<Map<String, dynamic>> put(
    String url, [
    Map<String, dynamic>? data,
  ]) async {
    try {
      final response = await http.put(
        Uri.parse(url),
        headers: _headers,
        body: data != null ? jsonEncode(data) : null,
      );

      return _processResponse(response);
    } catch (e) {
      return {'status': false, 'msg': 'Network error: ${e.toString()}'};
    }
  }

  Map<String, dynamic> _processResponse(http.Response response) {
    final responseData = jsonDecode(response.body);
    return responseData;
  }
}
