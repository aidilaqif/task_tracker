import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class FilterBottomSheet extends StatelessWidget {
  final String selectedFilter;
  final Function(String) onFilterSelected;

  const FilterBottomSheet({
    super.key,
    required this.selectedFilter,
    required this.onFilterSelected,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(AppTheme.spacingLg),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Filter Tasks', style: AppTheme.titleStyle),
          SizedBox(height: AppTheme.spacingMd),

          // All Tasks Option
          ListTile(
            leading: Icon(Icons.all_inclusive, color: AppTheme.primaryColor),
            title: Text('All Tasks'),
            trailing:
                selectedFilter == 'all'
                    ? Icon(Icons.check, color: AppTheme.primaryColor)
                    : null,
            onTap: () {
              onFilterSelected('all');
              Navigator.pop(context);
            },
          ),

          // Pending Tasks Option
          ListTile(
            leading: Icon(Icons.pending_outlined, color: AppTheme.pendingColor),
            title: Text('Pending'),
            trailing:
                selectedFilter == 'pending'
                    ? Icon(Icons.check, color: AppTheme.primaryColor)
                    : null,
            onTap: () {
              onFilterSelected('pending');
              Navigator.pop(context);
            },
          ),

          // In Progress Tasks Option
          ListTile(
            leading: Icon(Icons.play_arrow, color: AppTheme.inProgressColor),
            title: Text('In Progress'),
            trailing:
                selectedFilter == 'in-progress'
                    ? Icon(Icons.check, color: AppTheme.primaryColor)
                    : null,
            onTap: () {
              onFilterSelected('in-progress');
              Navigator.pop(context);
            },
          ),

          // Completed Tasks Option
          ListTile(
            leading: Icon(
              Icons.check_circle_outline,
              color: AppTheme.completedColor,
            ),
            title: Text('Completed'),
            trailing:
                selectedFilter == 'completed'
                    ? Icon(Icons.check, color: AppTheme.primaryColor)
                    : null,
            onTap: () {
              onFilterSelected('completed');
              Navigator.pop(context);
            },
          ),
        ],
      ),
    );
  }
}
