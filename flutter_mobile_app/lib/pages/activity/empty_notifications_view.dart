import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class EmptyNotificationsView extends StatelessWidget {
  const EmptyNotificationsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.notifications_none,
            size: 70,
            color: AppTheme.textSecondaryColor,
          ),
          SizedBox(height: AppTheme.spacingMd),
          Text('No notifications yet', style: AppTheme.titleStyle),
          SizedBox(height: AppTheme.spacingSm),
          Text(
            'New notifications will appear here',
            style: AppTheme.bodyStyle.copyWith(
              color: AppTheme.textSecondaryColor,
            ),
          ),
        ],
      ),
    );
  }
}
