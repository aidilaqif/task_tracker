import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class StatusTag extends StatelessWidget {
  final String status;
  final bool minified;
  const StatusTag({
    required this.status,
    this.minified = false,
  });

  String _getStatusLabel() {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Pending';
      case 'in-progress':
        return 'In Progress';
      case 'completed':
        return 'Completed';
      case 'request-extension':
        return 'Extension Requested';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: minified ? AppTheme.spacingXs : AppTheme.spacingSm,
        vertical: minified ? 2 : AppTheme.spacingXs,
      ),
      decoration: AppTheme.getStatusContainerDecoration(status),
      child: Text(
        _getStatusLabel(),
        style: AppTheme.getStatusTextStyle(status).copyWith(
          fontSize: minified ? 10 : null,
        ),
      ),
    );
  }
}