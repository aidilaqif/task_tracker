import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class NotificationPage extends StatelessWidget {
  const NotificationPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Notifications',
          style: TextStyle(
            color: AppTheme.textOnPrimaryColor,
            fontWeight: FontWeight.w600,
            fontSize: 20
          ),
        ),
        backgroundColor: AppTheme.primaryColor,
        elevation: AppTheme.elevationMd,
        iconTheme: IconThemeData(
          color: AppTheme.textOnPrimaryColor,
        ),
      ),
      body:  Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.notifications_none,
              size: 80,
              color: AppTheme.textSecondaryColor,
            ),
            SizedBox(height: AppTheme.spacingMd),
            Text(
              'This is the notification page',
              style: AppTheme.titleStyle,
            ),
             SizedBox(height: AppTheme.spacingSm),
            Text(
              'Notifications will appear here',
              style: AppTheme.bodyStyle.copyWith(
                color: AppTheme.textSecondaryColor,
              ),
            ),
          ],
        ),
      ),
    );
  }
}