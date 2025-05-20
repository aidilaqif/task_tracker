import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class TasksErrorView extends StatelessWidget {
  final String errorMessage;
  final VoidCallback onRetry;

  const TasksErrorView({
    super.key,
    required this.errorMessage,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 70, color: AppTheme.errorColor),
            SizedBox(height: AppTheme.spacingMd),
            Text('Error loading tasks', style: AppTheme.titleStyle),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              errorMessage,
              style: AppTheme.bodyStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingLg),
            ElevatedButton(onPressed: onRetry, child: Text('Try Again')),
          ],
        ),
      ),
    );
  }
}
