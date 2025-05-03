import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/pages/notification_page.dart';

class HomePage extends StatelessWidget {
  final Map<String, dynamic> userData;
  const HomePage({super.key, required this.userData});

  @override
  Widget build(BuildContext context) {
    // Extract user information from userData
    final userName = userData['user']['name'] ?? 'User';
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Task Tracker',
          style: TextStyle(
            color: AppTheme.textOnPrimaryColor,
            fontWeight: FontWeight.w600,
            fontSize: 20
          ),
        ),
        backgroundColor: AppTheme.primaryColor,
        actions: [
          Container(
            margin: EdgeInsets.only(right: AppTheme.spacingSm),
            child: IconButton(
              icon: Icon(
                Icons.notifications_outlined,
                color: AppTheme.textOnPrimaryColor,
                size: 28,
              ),
              tooltip: 'Notifications',
              onPressed: (){
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const NotificationPage(),
                  ),
                );
              },
            ),
          ),
        ],
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