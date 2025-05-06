import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/models/task_model.dart';
import 'package:flutter_mobile_app/widgets/priority_tag.dart';
import 'package:flutter_mobile_app/widgets/status_tag.dart';
import 'package:intl/intl.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class TaskCard extends StatelessWidget {
  final Task task;
  final VoidCallback onTap;

  const TaskCard({
    required this.task,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    // Date formatting
    final DateFormat dateFormat = DateFormat('MMM dd, yyyy');
    // Check if task is overdue
    final bool isOverdue = task.dueDate != null &&
      DateTime.now().isAfter(DateTime.parse(task.dueDate!)) &&
      task.status != 'completed';

    return Card(
      margin: EdgeInsets.only(bottom: AppTheme.spacingMd),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
        child: Padding(
          padding: EdgeInsets.all(AppTheme.spacingMd),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Title and Priority
              Row(
                children: [
                  Expanded(
                    child:Text(
                      task.title,
                      style: AppTheme.subtitleStyle,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  PriorityTag(priority: task.priority)
                ],
              ),
              SizedBox(height: AppTheme.spacingSm),
              //Description
              if (task.description.isNotEmpty)
                Text(
                  task.description,
                  style: AppTheme.bodyStyle.copyWith(
                    color: AppTheme.textSecondaryColor,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              SizedBox(height: AppTheme.spacingMd),
              // Status and Due Date
              Row(
                children: [
                  StatusTag(status: task.status),
                  SizedBox(width: AppTheme.spacingMd),
                  // Due date with calendar icon
                  Expanded(
                    child: Row(
                      children: [
                        Icon(
                          Icons.calendar_today_outlined,
                          size: 16,
                          color: isOverdue ? AppTheme.errorColor : AppTheme.textSecondaryColor,
                        ),
                        SizedBox(width: AppTheme.spacingMd),
                        Text(
                          task.dueDate != null
                            ? dateFormat.format(DateTime.parse(task.dueDate!))
                            : 'No due date',
                          style: AppTheme.captionStyle.copyWith(
                            color: isOverdue ? AppTheme.errorColor : AppTheme.textSecondaryColor,
                          ),
                        ),
                      ],
                    )
                  ),
                ],
              ),
              SizedBox(height: AppTheme.spacingMd),
              //Progress Bar
              ClipRRect(
                borderRadius: BorderRadius.circular(AppTheme.borderRadiusSm),
                child: LinearProgressIndicator(
                  value: (int.tryParse(task.progress) ?? 0) / 100.0,
                  backgroundColor: AppTheme.dividerColor,
                  valueColor: AlwaysStoppedAnimation<Color>(
                    AppTheme.getProgressColor(int.tryParse(task.progress) ?? 0),
                  ),
                  minHeight: 6,
                ),
              ),
              SizedBox(height: AppTheme.spacingSm),
              Text(
                '${task.progress}% complete',
                style: AppTheme.captionStyle,
              ),
            ],
          ),
        ),
      ),
    );
  }
}