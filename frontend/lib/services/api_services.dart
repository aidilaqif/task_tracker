import 'dart:convert';
import 'package:http/http.dart' as http;
import 'api_routes.dart';

class ApiService {
  Future<dynamic> checkConnection() async {
    final response = await http.get(Uri.parse(ApiRoutes.connection));

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to load data');
    }
  }

  Future<dynamic> loginUser(Map<String, dynamic> data) async {
    // final response = await http.post(
    //   Uri.parse(ApiRoutes.loginUser),
    //   headers: {'Content-Type': 'application/json'},
    //   body: jsonEncode(data),
    // );

    // if (response.statusCode == 201) {
    //   return jsonDecode(response.body);
    // } else {
    //   throw Exception('Failed to post data');
    // }

    try{
      final response = await http.post(
      Uri.parse(ApiRoutes.loginUser),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(data),
    );

    if (response.statusCode == 201) {
      return jsonDecode(response.body);
    }
    }catch(e){
      throw Exception(e);
    }
  }
}