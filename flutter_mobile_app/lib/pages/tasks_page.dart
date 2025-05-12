import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/task_model.dart';
import 'package:flutter_mobile_app/pages/tasks_detail_page.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:flutter_mobile_app/widgets/status_tag.dart';
import 'package:flutter_mobile_app/widgets/task_card.dart';

class TasksPage extends StatefulWidget {
  final Map<String, dynamic> userData;
  const TasksPage({super.key, required this.userData});

  @override
  State<TasksPage> createState() => TasksPageState();
}

class TasksPageState extends State<TasksPage> {
  final ApiService _apiService = ApiService();
  List<Task> _tasks = []; // Store all tasks from API
  List<Task> _filteredTasks = []; // Store filtered tasks when user applies filters
  bool _isLoading = true;
  String _errorMessage = '';
  String _selectedFilter = 'all';

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
        final taskList = (response['data'] as List?)
          ?.map((taskJson) => Task.fromJson(taskJson))
          .toList() ?? [];

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

    } catch(e) {
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
        borderRadius: BorderRadius.vertical(top: Radius.circular(AppTheme.borderRadiusLg)),
      ),
      builder: (context) {
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
                trailing: _selectedFilter == 'all' ? Icon(Icons.check, color: AppTheme.primaryColor) : null,
                onTap: () {
                  setState(() {
                    _selectedFilter = 'all';
                    _applyFilter();
                  });
                  Navigator.pop(context);
                },
              ),
              
              // Pending Tasks Option
              ListTile(
                leading: Icon(Icons.pending_outlined, color: AppTheme.pendingColor),
                title: Text('Pending'),
                trailing: _selectedFilter == 'pending' ? Icon(Icons.check, color: AppTheme.primaryColor) : null,
                onTap: () {
                  setState(() {
                    _selectedFilter = 'pending';
                    _applyFilter();
                  });
                  Navigator.pop(context);
                },
              ),
              
              // In Progress Tasks Option
              ListTile(
                leading: Icon(Icons.play_arrow, color: AppTheme.inProgressColor),
                title: Text('In Progress'),
                trailing: _selectedFilter == 'in-progress' ? Icon(Icons.check, color: AppTheme.primaryColor) : null,
                onTap: () {
                  setState(() {
                    _selectedFilter = 'in-progress';
                    _applyFilter();
                  });
                  Navigator.pop(context);
                },
              ),
              
              // Completed Tasks Option
              ListTile(
                leading: Icon(Icons.check_circle_outline, color: AppTheme.completedColor),
                title: Text('Completed'),
                trailing: _selectedFilter == 'completed' ? Icon(Icons.check, color: AppTheme.primaryColor) : null,
                onTap: () {
                  setState(() {
                    _selectedFilter = 'completed';
                    _applyFilter();
                  });
                  Navigator.pop(context);
                },
              ),
            ],
          ),
        );
      },
    );
  }

  void _applyFilter() {
    setState(() {
      if (_selectedFilter == 'all') {
        _filteredTasks = List.from(_tasks);
      } else {
        _filteredTasks = _tasks.where((task) => task.status == _selectedFilter).toList();
      }
    });
  }
  void _navigateToTaskDetails(Task task) {
    final userId = int.parse(widget.userData['user']['id'].toString());

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => TasksDetailPage(
          task: task,
          currentUserId: userId,
          onTaskUpdated: loadTasks,
        ),
      ),
    );
  }
  @override
  void initState() {
    super.initState();
    loadTasks();
  }

  Widget _buildErrorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Error icon
            Icon(
              Icons.error_outline,
              size: 70,
              color: AppTheme.errorColor,
            ),
            SizedBox(height: AppTheme.spacingMd),
            Text('Error loading tasks', style: AppTheme.titleStyle),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              _errorMessage,
              style: AppTheme.bodyStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingLg),
            ElevatedButton(
              onPressed: loadTasks,
              child: Text('Try Again')
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTaskList() {
    // If not tasks, show empty state
    if (_filteredTasks.isEmpty) {
      String emptyMessage = _selectedFilter == 'all'
        ? 'No tasks assigned yet'
        : 'No $_selectedFilter tasks';

      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.task_alt_outlined,
              size: 70,
              color: AppTheme.textSecondaryColor,
            ),
            SizedBox(height: AppTheme.spacingMd),
            Text(
              emptyMessage,
              style: AppTheme.titleStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              'New tasks will appear here',
              style: AppTheme.bodyStyle.copyWith(
                color: AppTheme.textSecondaryColor,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      );
    }

    return Column(
      children: [
        // Filter Indicator
        if (_selectedFilter != 'all')
          Padding(
            padding: EdgeInsets.fromLTRB(
              AppTheme.spacingMd,
              AppTheme.spacingMd,
              AppTheme.spacingMd,
              0
            ),
            child: Row(
              children: [
                Text(
                  'Filtered by: ',
                  style: AppTheme.bodyStyle,
                ),
                StatusTag(status: _selectedFilter),
                Spacer(),
                GestureDetector(
                  onTap: () {
                    setState(() {
                      _selectedFilter = 'all';
                      _applyFilter();
                    });
                  },
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
          ),
        Expanded(
          child: ListView.builder(
            physics: AlwaysScrollableScrollPhysics(),
            padding: EdgeInsets.all(AppTheme.spacingMd),
            itemCount: _filteredTasks.length,
            itemBuilder: (context, index) {
              return TaskCard(
                task: _filteredTasks[index],
                onTap: () => _navigateToTaskDetails(_filteredTasks[index]),
              );
            },
          ),
        ),
      ],
    );
  }
  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: loadTasks,
      child: _isLoading
      ? Center(child: CircularProgressIndicator())
      : _errorMessage.isNotEmpty
        ? _buildErrorView()
        : _buildTaskList(),
    );
  }
}