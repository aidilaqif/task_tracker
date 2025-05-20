import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/widgets/status_tag.dart';

class TaskFilterSection extends StatelessWidget {
  final String selectedFilter;
  final VoidCallback onClearFilter;

  const TaskFilterSection({
    super.key,
    required this.selectedFilter,
    required this.onClearFilter,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(
        AppTheme.spacingMd,
        AppTheme.spacingMd,
        AppTheme.spacingMd,
        0,
      ),
      child: Row(
        children: [
          Text('Filtered by: ', style: AppTheme.bodyStyle),
          StatusTag(status: selectedFilter),
          Spacer(),
          GestureDetector(
            onTap: onClearFilter,
            child: Text(
              'Clear Filter',
              style: TextStyle(
                color: AppTheme.primaryColor,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
