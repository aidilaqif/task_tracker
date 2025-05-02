import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/models/task_model.dart';

class TaskCard extends StatelessWidget {
  final Task task;
  final VoidCallback? onDelete;
  final Function(String)? onStatusChange;

  const TaskCard({
    super.key,
    required this.task,
    this.onDelete,
    this.onStatusChange,
  });

  Color _getPriorityColor() {
    switch (task.priority.toLowerCase()) {
      case 'high':
        return Colors.orange.shade100;
      case 'medium':
        return Colors.orange.shade100;
      case 'low':
        return Colors.green.shade100;
      default:
        return Colors.grey.shade100;
    }
  }

  Color _getStatusColor() {
    switch (task.status.toLowerCase()){
      case 'completed':
        return Colors.green;
      case 'in-progress':
        return Colors.blue;
      case 'pending':
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8.0, horizontal: 4.0),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8.0),
        side: BorderSide(color: _getPriorityColor(), width: 2.0),
      ),
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    task.title,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                PopupMenuButton<String>(
                  onSelected: (value) {
                    if (value == 'delete') {
                      onDelete?.call();
                    } else if (value.startsWith('status_')) {
                      final newStatus = value.substring(7);
                      onStatusChange?.call(newStatus);
                    }
                  },
                  itemBuilder:
                      (context) => [
                        const PopupMenuItem(
                          value: 'delete',
                          child: Text('Delete'),
                        ),
                        const PopupMenuItem(
                          value: 'status_pending',
                          child: Text('Set as Pending'),
                        ),
                        const PopupMenuItem(
                          value: 'status_in-progress',
                          child: Text('Set as In Progress'),
                        ),
                        const PopupMenuItem(
                          value: 'status_completed',
                          child: Text('Set as Completed'),
                        ),
                      ],
                  icon: const Icon(Icons.more_vert),
                ),
              ],
            ),
            const SizedBox(height: 8),
            if (task.description.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(bottom: 8.0),
                child: Text(
                  task.description,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(color: Colors.grey.shade700),
                ),
              ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: _getStatusColor(),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        task.statusLabel,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: _getPriorityColor(),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    task.priorityLabel,
                    style: TextStyle(color: Colors.grey.shade800, fontSize: 12),
                  ),
                ),
              ],
            ),
            if (task.dueDate != null)
              Text(
                'Due: ${task.dueDate}',
                style: TextStyle(fontSize: 12, color: Colors.grey.shade700),
              ),
          ],
        ),
      ),
    );
  }
}
