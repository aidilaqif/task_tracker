import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/custom_navigation_bar.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:flutter_mobile_app/services/socket_notification_service.dart';
import 'package:flutter_mobile_app/pages/login/app_logo.dart';
import 'package:flutter_mobile_app/pages/login/login_form.dart';
import 'package:flutter_mobile_app/pages/login/connection_status.dart';
import 'package:flutter_mobile_app/pages/login/login_button.dart';

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
      await apiService.checkConnection();

      setState(() {
        _isLoading = false;
        _connectionStatus = 'Connected to server successfully';
      });
    } catch (e) {
      setState(() {
        _isLoading = true;
        _connectionStatus = 'Failed to connect to server: ${e.toString()}';
      });
    }
  }

  void _login() async {
    // Validate inputs
    if (_emailController.text.isEmpty || _passwordController.text.isEmpty) {
      setState(() {
        _connectionStatus = 'Email and password are required';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _connectionStatus = 'Logging in...';
    });

    try {
      final ApiService apiService = ApiService();
      final loginData = {
        'email': _emailController.text,
        'password': _passwordController.text,
      };

      final response = await apiService.loginUser(loginData);

      if (response['status'] == true) {
        // Check if user role is 'user'
        if (response['data']['role'] == 'user') {
          // Initialize notification service
          int? userId;
          try {
            final userIdValue = response['data']['user']['id'];
            if (userIdValue != null) {
              userId = int.tryParse(userIdValue.toString()) ?? 0;
            } else {
              userId = 0;
            }

            if (userId > 0) {
              // Only initialize if we have a valid user ID
              SocketNotificationService().initSocket(userId);
            } else {
              print(
                'Warning: Invalid user ID for socket initialization: $userIdValue',
              );
            }
          } catch (e) {
            print('Error parsing user ID: ${e.toString()}');
          }
          // Navigate to home page and pass user data
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder:
                  (context) => CustomNavigationBar(userData: response['data']),
            ),
          );
        } else {
          setState(() {
            _isLoading = false;
            _connectionStatus =
                'This app is only for team members, not administrators';
          });
        }
      } else {
        setState(() {
          _isLoading = false;
          _connectionStatus = response['msg'] ?? 'Login failed';
        });
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
        _connectionStatus = 'Error: ${e.toString()}';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: AppTheme.lightTheme,
      child: Scaffold(
        body: Center(
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(AppTheme.spacingLg),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: <Widget>[
                  // App Logo
                  AppLogo(),
                  SizedBox(height: AppTheme.spacingXl),

                  // Login Form
                  LoginForm(
                    emailController: _emailController,
                    passwordController: _passwordController,
                  ),
                  SizedBox(height: AppTheme.spacingMd),

                  // Connection Status
                  if (_connectionStatus.isNotEmpty)
                    ConnectionStatus(connectionStatus: _connectionStatus),
                  SizedBox(height: AppTheme.spacingLg),

                  // Login Button
                  LoginButton(isLoading: _isLoading, onPressed: _login),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }
}
