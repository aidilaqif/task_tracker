import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class TaskProgressSection extends StatelessWidget {
  final String progress;
  final bool isAssignedToUser;
  final TextEditingController progressController;
  final bool isUpdating;
  final VoidCallback onUpdateProgress;

  const TaskProgressSection({
    super.key,
    required this.progress,
    required this.isAssignedToUser,
    required this.progressController,
    required this.isUpdating,
    required this.onUpdateProgress,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Progress', style: AppTheme.titleStyle),
        SizedBox(height: AppTheme.spacingSm),
        ClipRRect(
          borderRadius: BorderRadius.circular(AppTheme.borderRadiusSm),
          child: LinearProgressIndicator(
            value: int.parse(progress) / 100,
            backgroundColor: AppTheme.dividerColor,
            valueColor: AlwaysStoppedAnimation<Color>(
              AppTheme.getProgressColor(int.parse(progress)),
            ),
            minHeight: 12,
          ),
        ),
        SizedBox(height: AppTheme.spacingSm),
        Text('$progress% complete', style: AppTheme.bodyStyle),
        if (isAssignedToUser) ...[
          SizedBox(height: AppTheme.spacingLg),
          Text('Update Progress', style: AppTheme.titleStyle),
          SizedBox(height: AppTheme.spacingSm),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: progressController,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: 'Progress (%)',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(
                        AppTheme.borderRadiusMd,
                      ),
                    ),
                  ),
                ),
              ),
              SizedBox(width: AppTheme.spacingMd),
              ElevatedButton(
                onPressed: isUpdating ? null : onUpdateProgress,
                child:
                    isUpdating
                        ? SizedBox(
                          width: 24,
                          height: 24,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              AppTheme.textOnPrimaryColor,
                            ),
                          ),
                        )
                        : Text('Update'),
              ),
            ],
          ),
        ],
      ],
    );
  }
}
