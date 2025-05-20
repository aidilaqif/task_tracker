import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/task_model.dart';
import 'package:flutter_mobile_app/widgets/task_card.dart';
import 'package:flutter_mobile_app/pages/tasks/empty_tasks_view.dart';
import 'package:flutter_mobile_app/pages/tasks/task_filter_section.dart';

class TaskList extends StatelessWidget {
  final List<Task> filteredTasks;
  final String selectedFilter;
  final Function(Task) onTaskTap;
  final VoidCallback onClearFilter;

  const TaskList({
    super.key,
    required this.filteredTasks,
    required this.selectedFilter,
    required this.onTaskTap,
    required this.onClearFilter,
  });

  @override
  Widget build(BuildContext context) {
    // If no tasks, show empty state
    if (filteredTasks.isEmpty) {
      return EmptyTasksView(selectedFilter: selectedFilter);
    }

    return Column(
      children: [
        // Filter Indicator
        if (selectedFilter != 'all')
          TaskFilterSection(
            selectedFilter: selectedFilter,
            onClearFilter: onClearFilter,
          ),
        Expanded(
          child: ListView.builder(
            physics: AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.all(AppTheme.spacingMd),
            itemCount: filteredTasks.length,
            itemBuilder: (context, index) {
              return TaskCard(
                task: filteredTasks[index],
                onTap: () => onTaskTap(filteredTasks[index]),
              );
            },
          ),
        ),
      ],
    );
  }
}
