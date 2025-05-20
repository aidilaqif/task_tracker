import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/task_model.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:flutter_mobile_app/widgets/status_tag.dart';
import 'package:flutter_mobile_app/pages/task_detail/task_header.dart';
import 'package:flutter_mobile_app/pages/task_detail/reassignment_notice.dart';
import 'package:flutter_mobile_app/pages/task_detail/task_description_section.dart';
import 'package:flutter_mobile_app/pages/task_detail/task_info_row.dart';
import 'package:flutter_mobile_app/pages/task_detail/task_progress_section.dart';
import 'package:flutter_mobile_app/pages/task_detail/task_status_actions.dart';
import 'package:flutter_mobile_app/pages/task_detail/status_update_dialog.dart';
import 'package:intl/intl.dart';

class TasksDetailPage extends StatefulWidget {
  final Task task;
  final int currentUserId;
  final VoidCallback onTaskUpdated;

  const TasksDetailPage({
    super.key,
    required this.task,
    required this.currentUserId,
    required this.onTaskUpdated,
  });

  @override
  State<TasksDetailPage> createState() => _TasksDetailpageState();
}

class _TasksDetailpageState extends State<TasksDetailPage> {
  final ApiService _apiService = ApiService();
  late Task _task;
  bool _isUpdating = false;
  final TextEditingController _progressController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _task = widget.task;
    _progressController.text = _task.progress;
    _fetchTaskDetails();
    _markRelatedNotificationsAsRead();
  }

  Future<void> _updateTaskProgress() async {
    if (!_task.isAssignedToYou) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'You cannot update a task that is no longer assigned to you',
          ),
          backgroundColor: AppTheme.errorColor,
        ),
      );
      return;
    }
    final progress = int.tryParse(_progressController.text);
    if (progress == null || progress < 0 || progress > 100) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Please enter a valid progress value (0-100)'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
      return;
    }

    setState(() {
      _isUpdating = true;
    });

    try {
      final userId = int.parse(widget.task.userId.toString());
      final response = await _apiService.updateTaskProgress(
        _task.id,
        progress,
        userId,
      );

      if (response['status']) {
        final updatedTask = Task.fromJson(response['data']);
        setState(() {
          _task = Task(
            id: updatedTask.id,
            userId: updatedTask.userId,
            title: updatedTask.title,
            description: updatedTask.description,
            dueDate: updatedTask.dueDate,
            status: updatedTask.status,
            priority: updatedTask.priority,
            progress: updatedTask.progress,
            createdAt: updatedTask.createdAt,
            updatedAt: updatedTask.updatedAt,
            isAssignedToYou: _task.isAssignedToYou,
            assignedTo: _task.assignedTo,
          );
          _progressController.text = _task.progress;
          _isUpdating = false;
        });

        widget.onTaskUpdated(); // Refresh task list in the background
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Progress updated successfully'),
            backgroundColor: AppTheme.successColor,
          ),
        );
      } else {
        setState(() {
          _isUpdating = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['msg'] ?? 'Failed to update progress'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    } catch (e) {
      setState(() {
        _isUpdating = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error updating progress: ${e.toString()}'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
    }
  }

  Future<void> _updateTaskStatus(String newStatus) async {
    if (!_task.isAssignedToYou) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'You cannot update a task that is no longer assigned to you',
          ),
          backgroundColor: AppTheme.errorColor,
        ),
      );
      return;
    }
    setState(() {
      _isUpdating = true;
    });

    try {
      final userId = int.parse(widget.task.userId.toString());
      final response = await _apiService.updateTaskStatus(
        _task.id,
        newStatus,
        userId,
      );
      final updatedTask = Task.fromJson(response['data']);

      if (response['status']) {
        setState(() {
          _task = Task(
            id: updatedTask.id,
            userId: updatedTask.userId,
            title: updatedTask.title,
            description: updatedTask.description,
            dueDate: updatedTask.dueDate,
            status: updatedTask.status,
            priority: updatedTask.priority,
            progress: updatedTask.progress,
            createdAt: updatedTask.createdAt,
            updatedAt: updatedTask.updatedAt,
            isAssignedToYou: _task.isAssignedToYou,
            assignedTo: _task.assignedTo,
          );
          _isUpdating = false;
        });

        widget.onTaskUpdated(); // Refresh task list in the background
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Status updated successfully'),
            backgroundColor: AppTheme.successColor,
          ),
        );
      } else {
        setState(() {
          _isUpdating = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['msg'] ?? 'Failed to update status'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    } catch (e) {
      setState(() {
        _isUpdating = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error updating status: ${e.toString()}'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
    }
  }

  Future<void> _markRelatedNotificationsAsRead() async {
    try {
      final userId = int.parse(widget.currentUserId.toString());
      final response = await _apiService.getUserNotifications(
        userId,
        isRead: false,
      );

      if (response['status'] && response['data'] != null) {
        final notifications = response['data'] as List;

        // Find all unread notifications for this task
        for (var notification in notifications) {
          if (notification['task_id'] != null &&
              int.tryParse(notification['task_id'].toString()) ==
                  widget.task.id) {
            // Mark this notification as read
            final notificationId = int.tryParse(notification['id'].toString());
            if (notificationId != null) {
              await _apiService.markNotificationAsRead(notificationId);
            }
          }
        }
      }
    } catch (e) {
      print('Error marking related notifications as read: $e');
    }
  }

  void _showStatusUpdateDialog() {
    showDialog(
      context: context,
      builder:
          (context) => StatusUpdateDialog(onStatusSelected: _updateTaskStatus),
    );
  }

  Future<void> _fetchTaskDetails() async {
    try {
      final response = await _apiService.viewTask(
        widget.task.id,
        userId: widget.currentUserId,
      );

      if (response['status']) {
        setState(() {
          _task = Task.fromJson(response['data']);
        });
        print('Task fetched with isAssignedToYou: ${_task.isAssignedToYou}');
      }
    } catch (e) {
      print('Error fetching task details: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    final DateFormat dateFormat = DateFormat('MMM dd, yyyy');

    return Scaffold(
      appBar: AppBar(
        title: Text('Task Details'),
        backgroundColor: AppTheme.primaryColor,
        foregroundColor: AppTheme.textOnPrimaryColor,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Reassignment notice
            if (!_task.isAssignedToYou)
              ReassignmentNotice(assignedTo: _task.assignedTo),

            // Title and Priority
            TaskHeader(task: _task),

            SizedBox(height: AppTheme.spacingMd),

            // Status
            StatusTag(status: _task.status),

            SizedBox(height: AppTheme.spacingMd),

            // Description
            TaskDescriptionSection(description: _task.description),

            SizedBox(height: AppTheme.spacingLg),

            // Due Date
            TaskInfoRow(
              icon: Icons.calendar_today_outlined,
              label:
                  'Due Date: ${_task.dueDate != null ? dateFormat.format(DateTime.parse(_task.dueDate!)) : 'No due date'}',
            ),

            SizedBox(height: AppTheme.spacingMd),

            // Assignee info
            TaskInfoRow(
              icon: Icons.person_outline,
              label: 'Assigned to: ${_task.assignedTo ?? 'Unassigned'}',
              trailing:
                  _task.isAssignedToYou
                      ? [
                        Container(
                          margin: EdgeInsets.only(left: AppTheme.spacingMd),
                          padding: EdgeInsets.symmetric(
                            horizontal: AppTheme.spacingSm,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: AppTheme.primaryColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusSm,
                            ),
                          ),
                          child: Text(
                            'You',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                              color: AppTheme.primaryColor,
                            ),
                          ),
                        ),
                      ]
                      : null,
            ),

            SizedBox(height: AppTheme.spacingLg),

            // Progress
            TaskProgressSection(
              progress: _task.progress,
              isAssignedToUser: _task.isAssignedToYou,
              progressController: _progressController,
              isUpdating: _isUpdating,
              onUpdateProgress: _updateTaskProgress,
            ),

            SizedBox(height: AppTheme.spacingLg),

            // Action Buttons
            TaskStatusActions(
              isAssignedToUser: _task.isAssignedToYou,
              isCompleted: _task.status == 'completed',
              isUpdating: _isUpdating,
              onUpdateStatus: _showStatusUpdateDialog,
            ),
          ],
        ),
      ),
    );
  }

  @override
  void dispose() {
    _progressController.dispose();
    super.dispose();
  }
}
