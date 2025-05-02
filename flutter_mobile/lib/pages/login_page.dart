import 'package:flutter/material.dart';
import 'package:frontend/widgets/custom_form_field.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  String _connectionStatus = '';
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    // checkConnection();
  }

  void _submit() async {
    if (_emailController.text.isEmpty || _passwordController.text.isEmpty) {
      setState(() {
        _connectionStatus = 'Email and password are required';
      });
      return;
    }

    setState(() {
      isLoading = true;
      _connectionStatus = '';
    });
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
              isLoading
                  ? CircularProgressIndicator()
                  : ElevatedButton(
                    onPressed: _submit,
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