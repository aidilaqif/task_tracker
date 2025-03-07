import 'package:flutter/material.dart';
import 'package:frontend/services/api_services.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final ApiService apiService = ApiService();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  String _submittedValue = '';
  List<dynamic> data = [];
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    checkConnection();
  }

  void _submit() async {
    setState(() {
      isLoading = true;
    });

    try {
      final response = await apiService.loginUser({
        'email': _emailController.text,
        'password': _passwordController.text,
      });

      setState(() {
        _submittedValue = response['msg'];
      });

      if (response['status']) {
        print('Login successful: ${response['data']}');
      } else {
        print('Login failed: ${response['msg']}');
      }
    } catch (e) {
      setState(() {
        _submittedValue = 'Error: $e';
        isLoading = false;
      });
    }
  }

  void checkConnection() async {
    try {
      var result = await apiService.checkConnection();
      setState(() {
        data = result;
        isLoading = false;
      });
    } catch (e) {
      setState(() {
        isLoading = false;
      });
      // print(e);
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
              FormField(
                isHidden: false,
                labelText: "Email",
                controller: _emailController,
              ),
              FormField(
                isHidden: true,
                labelText: "Password",
                controller: _passwordController,
              ),
              isLoading
                  ? CircularProgressIndicator()
                  : ElevatedButton(onPressed: _submit, child: Text('submit')),
              Text('Submitted Value: $_submittedValue'),
            ],
          ),
        ),
      ),
    );
  }
}

class FormField extends StatelessWidget {
  const FormField({
    super.key,
    required this.isHidden,
    required this.labelText,
    required this.controller,
  });

  final bool isHidden;
  final String labelText;
  final TextEditingController controller;

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      obscureText: isHidden,
      decoration: InputDecoration(
        border: OutlineInputBorder(),
        labelText: labelText,
      ),
    );
  }
}
