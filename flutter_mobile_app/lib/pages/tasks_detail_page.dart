import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/task_model.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:flutter_mobile_app/widgets/priority_tag.dart';
import 'package:flutter_mobile_app/widgets/status_tag.dart';
import 'package:intl/intl.dart';

class TasksDetailPage extends StatefulWidget {
  final Task task;
  final VoidCallback onTaskUpdated;

  const TasksDetailPage({
    super.key,
    required this.task,
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

  Future<void> _updateTaskProgress() async {
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
      final response = await _apiService.updateTaskProgress(_task.id, progress);

      if (response['status']) {
        setState(() {
          _task = Task.fromJson(response['data']);
          _progressController.text = _task.progress;
          _isUpdating = false;
        });

        widget.onTaskUpdated();  // Refresh task list in the background
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
    setState(() {
      _isUpdating = true;
    });

    try {
      final response = await _apiService.updateTaskStatus(_task.id, newStatus);

      if (response['status']) {
        setState(() {
          _task = Task.fromJson(response['data']);
          _isUpdating = false;
        });

        widget.onTaskUpdated();  // Refresh task list in the background
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

  void _showStatusUpdateDialog() {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text('Update Status', style: AppTheme.titleStyle),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                title: Text('In Progress'),
                onTap: () {
                  Navigator.pop(context);
                  _updateTaskStatus('in-progress');
                },
              ),
              ListTile(
                title: Text('Complete'),
                onTap: () {
                  Navigator.pop(context);
                  _updateTaskStatus('completed');
                },
              ),
              ListTile(
                title: Text('Request Extension'),
                onTap: () {
                  Navigator.pop(context);
                  _updateTaskStatus('request-extension');
                },
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text('Cancel'),
            ),
          ],
        );
      },
    );
  }

  @override
  void initState() {
    super.initState();
    _task = widget.task;
    _progressController.text = _task.progress;
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
            // Title and Priority
            Row(
              children: [
                Expanded(
                  child: Text(
                    _task.title,
                    style: AppTheme.headlineStyle,
                  ),
                ),
                PriorityTag(priority: _task.priority),
              ],
            ),
            SizedBox(height: AppTheme.spacingMd),
            // Status
            StatusTag(status: _task.status),
            SizedBox(height: AppTheme.spacingMd),
            // Description
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
                _task.description.isEmpty ? 'No description provided' : _task.description,
                style: AppTheme.bodyStyle,
              ),
            ),
            SizedBox(height: AppTheme.spacingLg),
            // Due Date
            Row(
              children: [
                Icon(Icons.calendar_today_outlined, size: 20, color: AppTheme.primaryColor),
                SizedBox(width: AppTheme.spacingSm),
                Text(
                  'Due Date: ${_task.dueDate != null ? dateFormat.format(DateTime.parse(_task.dueDate!)) : 'No due date'}',
                  style: AppTheme.subtitleStyle,
                ),
              ],
            ),
            SizedBox(height: AppTheme.spacingLg),
            // Progress
            Text('Progress', style: AppTheme.titleStyle),
            SizedBox(height: AppTheme.spacingSm),
            ClipRRect(
              borderRadius: BorderRadius.circular(AppTheme.borderRadiusSm),
              child: LinearProgressIndicator(
                value: int.parse(_task.progress) / 100,
                backgroundColor: AppTheme.dividerColor,
                valueColor: AlwaysStoppedAnimation<Color>(
                  AppTheme.getProgressColor(int.parse(_task.progress)),
                ),
                minHeight: 12,
              ),
            ),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              '${_task.progress}% complete',
              style: AppTheme.bodyStyle,
            ),
            SizedBox(height: AppTheme.spacingLg),
            // Update Progress
            Text('Update Progress', style: AppTheme.titleStyle),
            SizedBox(height: AppTheme.spacingSm),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _progressController,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      labelText: 'Progress (%)',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
                      ),
                    ),
                  ),
                ),
                SizedBox(width: AppTheme.spacingMd),
                ElevatedButton(
                  onPressed: (){
                    _updateTaskProgress();
                  },
                  child: _isUpdating
                    ? SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(AppTheme.textOnPrimaryColor),
                      ),
                    )
                    : Text('Update'),
                ),
              ],
            ),
            SizedBox(height: AppTheme.spacingLg),
            // Action Buttons
            if (_task.status != 'completed')
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton(
                      onPressed: _isUpdating ? null : _showStatusUpdateDialog,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor,
                      ),
                      child: Text('Update Status', style: TextStyle(color: AppTheme.textOnPrimaryColor)),
                    ),
                  ),
                ],
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