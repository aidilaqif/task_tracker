import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class TaskDescriptionSection extends StatelessWidget {
  final String description;

  const TaskDescriptionSection({super.key, required this.description});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Description', style: AppTheme.titleStyle),
        SizedBox(height: AppTheme.spacingSm),
        Container(
          padding: EdgeInsets.all(AppTheme.spacingMd),
          decoration: BoxDecoration(
            color: AppTheme.scaffoldBackgroundColor,
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
            border: Border.all(color: AppTheme.dividerColor),
          ),
          child: Text(
            description.isEmpty ? 'No description provided' : description,
            style: AppTheme.bodyStyle,
          ),
        ),
      ],
    );
  }
}
