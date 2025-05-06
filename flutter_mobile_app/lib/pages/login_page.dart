import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/custom_navigation_bar.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/services/api_services.dart';

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
          // Navigate to home page and pass user data
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder: (context) => CustomNavigationBar(userData: response['data']),
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
                  Icon(
                    Icons.assignment_outlined,
                    size: 70,
                    color: AppTheme.primaryColor,
                  ),
                  SizedBox(height: AppTheme.spacingMd),
                  Text(
                    'Task Tracker',
                    style: AppTheme.headlineStyle,
                  ),
                  Text(
                    'Team Member Login',
                    style: AppTheme.subtitleStyle.copyWith(
                      color: AppTheme.textSecondaryColor,
                    ),
                  ),
                  SizedBox(height: AppTheme.spacingXl),
                  TextField(
                    controller: _emailController,
                    decoration: InputDecoration(
                      labelText: 'Email',
                      prefixIcon: Icon(Icons.email, color: AppTheme.primaryColor),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
                      ),
                    ),
                    keyboardType: TextInputType.emailAddress,
                  ),
                  SizedBox(height: AppTheme.spacingMd),
                  TextField(
                    controller: _passwordController,
                    decoration: InputDecoration(
                      labelText: 'Password',
                      prefixIcon: Icon(Icons.lock, color: AppTheme.primaryColor),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
                      ),
                    ),
                    obscureText: true,
                  ),
                  SizedBox(height: AppTheme.spacingMd),
                  if (_connectionStatus.isNotEmpty)
                    Container(
                      padding: EdgeInsets.all(AppTheme.spacingSm),
                      decoration: BoxDecoration(
                        color: _connectionStatus.contains('successful') || _connectionStatus.contains('Connected')
                              ? AppTheme.lowPriorityBgColor : AppTheme.highPriorityBgColor,
                        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
                      ),
                      child: Text(
                        _connectionStatus,
                        style: TextStyle(
                          color: _connectionStatus.contains('successful') || _connectionStatus.contains('Connected')
                                ? AppTheme.lowPriorityColor : AppTheme.highPriorityColor,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  SizedBox(height: AppTheme.spacingLg),
                  _isLoading
                    ? CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primaryColor),
                      )
                    : ElevatedButton(
                        onPressed: _login,
                        style: ElevatedButton.styleFrom(
                          minimumSize: Size(double.infinity, 50),
                          backgroundColor: AppTheme.primaryColor,
                          foregroundColor: AppTheme.textOnPrimaryColor,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
                          ),
                        ),
                        child: Text(
                          'Login',
                          style: AppTheme.buttonTextStyle,
                        ),
                      )
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
