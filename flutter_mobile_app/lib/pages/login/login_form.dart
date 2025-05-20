import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class LoginForm extends StatelessWidget {
  final TextEditingController emailController;
  final TextEditingController passwordController;

  const LoginForm({
    super.key,
    required this.emailController,
    required this.passwordController,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        TextField(
          controller: emailController,
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
          controller: passwordController,
          decoration: InputDecoration(
            labelText: 'Password',
            prefixIcon: Icon(Icons.lock, color: AppTheme.primaryColor),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
            ),
          ),
          obscureText: true,
        ),
      ],
    );
  }
}
