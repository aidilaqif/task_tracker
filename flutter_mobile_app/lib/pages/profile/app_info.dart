import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class AppInfo extends StatelessWidget {
  const AppInfo({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Divider(height: AppTheme.spacingLg),
        SizedBox(height: AppTheme.spacingMd),
        Text(
          'Task Tracker Mobile',
          style: AppTheme.captionStyle.copyWith(
            color: AppTheme.textSecondaryColor,
          ),
        ),
        SizedBox(height: AppTheme.spacingSm),
        Text(
          'Version 1.0.0',
          style: AppTheme.captionStyle.copyWith(
            color: AppTheme.textSecondaryColor,
          ),
        ),
        SizedBox(height: AppTheme.spacingSm),
        Text(
          '© 2025 Task Tracker',
          style: AppTheme.captionStyle.copyWith(
            color: AppTheme.textSecondaryColor,
          ),
        ),
      ],
    );
  }
}
