import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class ReassignmentNotice extends StatelessWidget {
  final String? assignedTo;

  const ReassignmentNotice({super.key, this.assignedTo});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(AppTheme.spacingMd),
      margin: EdgeInsets.only(bottom: AppTheme.spacingLg),
      decoration: BoxDecoration(
        color: AppTheme.warningColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
        border: Border.all(color: AppTheme.warningColor),
      ),
      child: Row(
        children: [
          Icon(Icons.info_outline, color: AppTheme.warningColor, size: 24),
          SizedBox(width: AppTheme.spacingMd),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "This task has been reassigned",
                  style: AppTheme.subtitleStyle.copyWith(
                    color: AppTheme.warningColor,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                SizedBox(height: AppTheme.spacingXs),
                Text(
                  "This task is now assigned to ${assignedTo ?? 'someone else'}. You can view it, but cannot make changes.",
                  style: TextStyle(color: AppTheme.warningColor),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
