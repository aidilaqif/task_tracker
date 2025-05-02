import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class HomePage extends StatelessWidget {
  final Map<String, dynamic> userData;
  const HomePage({super.key, required this.userData});

  @override
  Widget build(BuildContext context) {
    // Extract user information from userData
    final userName = userData['user']['name'] ?? 'User';
    return Scaffold(
      appBar: AppBar(
        title: Text('Task Tracker'),
        backgroundColor: AppTheme.primaryColor,
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              'Welcome $userName',
              style: AppTheme.titleStyle,
            ),
            SizedBox(height: AppTheme.spacingMd),
            Text(
              'Task will be implemented here',
              style: AppTheme.bodyStyle
            ),
          ],
        ),
      ),
    );
  }
}