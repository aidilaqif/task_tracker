import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class EmptyTasksView extends StatelessWidget {
  final String selectedFilter;

  const EmptyTasksView({super.key, required this.selectedFilter});

  @override
  Widget build(BuildContext context) {
    String emptyMessage =
        selectedFilter == 'all'
            ? 'No tasks assigned yet'
            : 'No $selectedFilter tasks';

    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.task_alt_outlined,
            size: 70,
            color: AppTheme.textSecondaryColor,
          ),
          SizedBox(height: AppTheme.spacingMd),
          Text(
            emptyMessage,
            style: AppTheme.titleStyle,
            textAlign: TextAlign.center,
          ),
          SizedBox(height: AppTheme.spacingSm),
          Text(
            'New tasks will appear here',
            style: AppTheme.bodyStyle.copyWith(
              color: AppTheme.textSecondaryColor,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
