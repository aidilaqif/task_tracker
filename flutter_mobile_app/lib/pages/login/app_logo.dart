import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class AppLogo extends StatelessWidget {
  const AppLogo({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(Icons.assignment_outlined, size: 70, color: AppTheme.primaryColor),
        SizedBox(height: AppTheme.spacingMd),
        Text('Task Tracker', style: AppTheme.headlineStyle),
        Text(
          'Team Member Login',
          style: AppTheme.subtitleStyle.copyWith(
            color: AppTheme.textSecondaryColor,
          ),
        ),
      ],
    );
  }
}
