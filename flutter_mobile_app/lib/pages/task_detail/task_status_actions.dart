import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class TaskStatusActions extends StatelessWidget {
  final bool isAssignedToUser;
  final bool isCompleted;
  final bool isUpdating;
  final VoidCallback onUpdateStatus;

  const TaskStatusActions({
    super.key,
    required this.isAssignedToUser,
    required this.isCompleted,
    required this.isUpdating,
    required this.onUpdateStatus,
  });

  @override
  Widget build(BuildContext context) {
    if (isCompleted) {
      return SizedBox();
    }

    if (!isAssignedToUser) {
      return Container(
        padding: EdgeInsets.all(AppTheme.spacingMd),
        decoration: BoxDecoration(
          color: AppTheme.scaffoldBackgroundColor,
          borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
          border: Border.all(color: AppTheme.dividerColor),
        ),
        child: Row(
          children: [
            Icon(Icons.lock_outline, color: AppTheme.textSecondaryColor),
            SizedBox(width: AppTheme.spacingMd),
            Expanded(
              child: Text(
                'You cannot update this task as it is no longer assigned to you.',
                style: AppTheme.bodyStyle.copyWith(
                  color: AppTheme.textSecondaryColor,
                ),
              ),
            ),
          ],
        ),
      );
    }

    return Row(
      children: [
        Expanded(
          child: ElevatedButton(
            onPressed: isUpdating ? null : onUpdateStatus,
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryColor,
            ),
            child: Text(
              'Update Status',
              style: TextStyle(color: AppTheme.textOnPrimaryColor),
            ),
          ),
        ),
      ],
    );
  }
}
