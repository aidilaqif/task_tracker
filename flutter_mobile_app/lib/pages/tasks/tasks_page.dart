import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/task_model.dart';
import 'package:flutter_mobile_app/pages/tasks/error_view.dart';
import 'package:flutter_mobile_app/pages/tasks/filter_bottom_sheet.dart';
import 'package:flutter_mobile_app/pages/tasks/task_list.dart';
import 'package:flutter_mobile_app/pages/task_detail/tasks_detail_page.dart';
import 'package:flutter_mobile_app/services/api_services.dart';

class TasksPage extends StatefulWidget {
  final Map<String, dynamic> userData;
  const TasksPage({super.key, required this.userData});

  @override
  State<TasksPage> createState() => TasksPageState();
}

class TasksPageState extends State<TasksPage> {
  final ApiService _apiService = ApiService();
  List<Task> _tasks = []; // Store all tasks from API
  List<Task> _filteredTasks =
      []; // Store filtered tasks when user applies filters
  bool _isLoading = true;
  String _errorMessage = '';
  String _selectedFilter = 'all';

  @override
  void initState() {
    super.initState();
    loadTasks();
  }

  Future<void> loadTasks() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      // Get user ID from userData passed to widget
      final userId = int.parse(widget.userData['user']['id'].toString());

      // Call API to get tasks
      final response = await _apiService.getUserTasks(userId);

      // Check if the request was successful
      if (response['status']) {
        // Parse task data from response
        final taskList =
            (response['data'] as List?)
                ?.map((taskJson) => Task.fromJson(taskJson))
                .toList() ??
            [];

        setState(() {
          _tasks = taskList;
          _applyFilter();
          _isLoading = false;
        });
      } else {
        // Handle API errors
        setState(() {
          _errorMessage = response['msg'] ?? 'Failed to load tasks';
          _isLoading = false;
        });
      }
    } catch (e) {
      // Handle exceptions
      setState(() {
        _errorMessage = 'Error loading tasks: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  void showFilterOptions() {
    showModalBottomSheet(
      context: context,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(
          top: Radius.circular(AppTheme.borderRadiusLg),
        ),
      ),
      builder: (context) {
        return FilterBottomSheet(
          selectedFilter: _selectedFilter,
          onFilterSelected: (filter) {
            setState(() {
              _selectedFilter = filter;
              _applyFilter();
            });
          },
        );
      },
    );
  }

  void _applyFilter() {
    setState(() {
      if (_selectedFilter == 'all') {
        _filteredTasks = List.from(_tasks);
      } else {
        _filteredTasks =
            _tasks.where((task) => task.status == _selectedFilter).toList();
      }
    });
  }

  void _navigateToTaskDetails(Task task) {
    final userId = int.parse(widget.userData['user']['id'].toString());

    Navigator.push(
      context,
      MaterialPageRoute(
        builder:
            (context) => TasksDetailPage(
              task: task,
              currentUserId: userId,
              onTaskUpdated: loadTasks,
            ),
      ),
    );
  }

  void _clearFilter() {
    setState(() {
      _selectedFilter = 'all';
      _applyFilter();
    });
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: loadTasks,
      child:
          _isLoading
              ? Center(child: CircularProgressIndicator())
              : _errorMessage.isNotEmpty
              ? TasksErrorView(errorMessage: _errorMessage, onRetry: loadTasks)
              : TaskList(
                filteredTasks: _filteredTasks,
                selectedFilter: _selectedFilter,
                onTaskTap: _navigateToTaskDetails,
                onClearFilter: _clearFilter,
              ),
    );
  }
}
