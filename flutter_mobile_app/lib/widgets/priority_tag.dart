import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class PriorityTag extends StatelessWidget {
  final String priority;
  const PriorityTag({super.key, required this.priority});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: AppTheme.spacingSm,
        vertical: AppTheme.spacingXs,
      ),
      decoration: AppTheme.getPriorityContainerDecoration(priority),
      child: Text(
        priority,
        style: AppTheme.getPriorityTextStyle(priority),
      ),
    );
  }
}