import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class StatusTag extends StatelessWidget {
  final String status;
  const StatusTag({required this.status});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: AppTheme.spacingSm,
        vertical: AppTheme.spacingXs,
      ),
      decoration: AppTheme.getStatusContainerDecoration(status),
      child: Text(
        status,
        style: AppTheme.getStatusTextStyle(status),
      ),
    );
  }
}