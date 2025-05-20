import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/task_model.dart';
import 'package:flutter_mobile_app/widgets/priority_tag.dart';

class TaskHeader extends StatelessWidget {
  final Task task;

  const TaskHeader({super.key, required this.task});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(child: Text(task.title, style: AppTheme.headlineStyle)),
        PriorityTag(priority: task.priority),
      ],
    );
  }
}
