import 'package:flutter/material.dart';
import 'package:frontend/pages/login_page.dart';
import 'package:frontend/services/api_services.dart';
import 'package:frontend/widgets/custom_form_field.dart';

class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final ApiService apiService = ApiService();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _confirmPasswordController =
      TextEditingController();

  String _statusMessage = '';
  bool _isLoading = false;

  void _register() async {
    // Form validation
    if (_nameController.text.isEmpty ||
        _emailController.text.isEmpty ||
        _passwordController.text.isEmpty ||
        _confirmPasswordController.text.isEmpty) {
      setState(() {
        _statusMessage = 'All fields are required';
      });
      return;
    }

    if (_passwordController.text != _confirmPasswordController.text) {
      setState(() {
        _statusMessage = 'Passwords do not match';
      });
      return;
    }

    // Email validation with simple regex
    final emailRegex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
    if (!emailRegex.hasMatch(_emailController.text)) {
      setState(() {
        _statusMessage = 'Please enter a valid email address';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _statusMessage = '';
    });

    try {
      final response = await apiService.registerUser({
        'name': _nameController.text,
        'email': _emailController.text,
        'password': _passwordController.text,
      });

      setState(() {
        _statusMessage = response['msg'] ?? 'Unknown response';
        _isLoading = false;
      });

      if (response['status'] == true) {
        // Show success message and navigate to login page
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Registration successful! Please login.', style: TextStyle(color: Colors.green),)),
        );

        // Show delay before navigating to login
        Future.delayed(Duration(seconds: 1), () {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => LoginPage()),
          );
        });
      }
    } catch (e) {
      setState(() {
        _statusMessage = 'Error: $e';
        _isLoading = false;
      });
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
                'Create an Account',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              CustomFormField(
                isHidden: false,
                labelText: "Name",
                controller: _nameController,
                prefixIcon: Icons.person,
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
              CustomFormField(
                isHidden: true,
                labelText: "Confirm Password",
                controller: _confirmPasswordController,
                prefixIcon: Icons.lock_outline,
              ),
              if (_statusMessage.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.only(bottom: 16.0),
                  child: Text(
                    _statusMessage,
                    style: TextStyle(
                      color:
                          _statusMessage.contains('successful')
                              ? Colors.green
                              : Colors.red,
                    ),
                  ),
                ),
              _isLoading
                  ? CircularProgressIndicator()
                  : ElevatedButton(
                    onPressed: _register,
                    style: ElevatedButton.styleFrom(
                      minimumSize: Size(double.infinity, 50),
                    ),
                    child: Text('Register', style: TextStyle(fontSize: 16)),
                  ),
              TextButton(
                onPressed: () {
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(builder: (context) => LoginPage()),
                  );
                },
                child: Text('Already have an account? Login'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
