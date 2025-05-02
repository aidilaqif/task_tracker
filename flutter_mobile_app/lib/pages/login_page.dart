import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/widgets/custom_form_field.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:flutter_mobile_app/pages/home_page.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  String _connectionStatus = '';
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _checkConnection();
  }

  Future<void> _checkConnection() async {
    setState(() {
      _isLoading = true;
      _connectionStatus = 'Checking connection';
    });

    try {
      final ApiService apiService = ApiService();
      final result = await apiService.checkConnection();

      setState(() {
        _isLoading = false;
        _connectionStatus = 'Connected to server successfully';
      });
      print(_connectionStatus);
    } catch (e) {
      setState(() {
        _isLoading = true;
        _connectionStatus = 'Failed to connect to server: ${e.toString()}';
      });
      print(_connectionStatus);
    }
  }



  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: SizedBox(
          width: 300,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            spacing: 20.0,
            children: <Widget>[
              Text(
                'Task Tracker',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              CustomFormField(
                isHidden: false,
                labelText: "Email",
                controller: _emailController,
                prefixIcon: Icons.email,
              ),
              CustomFormField(
                isHidden: true,
                labelText: "Password",
                controller: _passwordController,
                prefixIcon: Icons.lock,
              ),
              if (_connectionStatus.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(bottom: 16.0),
                  child: Text(
                    _connectionStatus,
                    style: TextStyle(
                      color:
                          _connectionStatus.contains('successful') ||
                                  _connectionStatus.contains('connected')
                              ? Colors.green
                              : Colors.red,
                    ),
                  ),
                ),
              _isLoading
                  ? CircularProgressIndicator()
                  : ElevatedButton(
                    onPressed: (){},
                    style: ElevatedButton.styleFrom(
                      minimumSize: Size(double.infinity, 50),
                    ),
                    child: Text('Login', style: TextStyle(fontSize: 16)),
                  ),
            ],
          ),
        ),
      ),
    );
  }
}