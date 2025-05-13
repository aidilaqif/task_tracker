import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/task_model.dart';
import 'package:flutter_mobile_app/pages/tasks_detail_page.dart';
import 'package:flutter_mobile_app/services/notification_service.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:intl/intl.dart';

class ActivityPage extends StatefulWidget {
  final Map<String, dynamic> userData;
  const ActivityPage({super.key, required this.userData});

  void refreshNotifications() {
    final state = _ActivityPageState.of(this);
    state?.loadNotifications();
  }

  @override
  State<ActivityPage> createState() => _ActivityPageState();
}

class _ActivityPageState extends State<ActivityPage> {
  final ApiService _apiService = ApiService();
  final NotificationService _notificationService = NotificationService();
  final List<Map<String, dynamic>> _notifications = [];
  bool _isLoading = true;
  String _errorMessage = '';
  static _ActivityPageState? of(ActivityPage widget) {
    return _ActivityPageStates[widget];
  }
  static final Map<ActivityPage, _ActivityPageState> _ActivityPageStates = {};

  @override
  void initState() {
    super.initState();
    _ActivityPageStates[widget] = this;
    _loadNotifications();
    
    // Listen for new notifications
    _notificationService.notificationStream.listen((notification) {
      _addNotification(notification);
    });
  }

  Future<void> _loadNotifications() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      // Get user ID from userData
      final userId = int.parse(widget.userData['user']['id'].toString());
      
      // Fetch notifications from API
      final response = await _apiService.getUserNotifications(userId);
      
      if (response['status']) {
        setState(() {
          // Clear existing notifications
          _notifications.clear();
          
          // Add notifications from response
          if (response['data'] != null && response['data'] is List) {
            for (var notification in response['data']) {
              _notifications.add(notification);
            }
          }
          
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = response['msg'] ?? 'Failed to load notifications';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Error loading notifications: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  void _addNotification(Map<String, dynamic> notification) {
    // Check if notification already exists
    final existingIndex = _notifications.indexWhere((n) => n['id'] == notification['id']);
    
    setState(() {
      if (existingIndex >= 0) {
        // Update existing notification
        _notifications[existingIndex] = notification;
      } else {
        // Add new notification to the beginning of the list
        _notifications.insert(0, notification);
      }
    });
  }

  Future<void> _markAsRead(int notificationId) async {
    try {
      // Show loading indicator
      setState(() {
        // Find notification and mark as processing
        final index = _notifications.indexWhere((n) => n['id'].toString() == notificationId.toString());
        if (index >= 0) {
          _notifications[index]['processing'] = true;
        }
      });
      
      // Convert to int
      final id = int.parse(notificationId.toString());
      
      final response = await _apiService.markNotificationAsRead(id);
      
      // Always update UI regardless of response status
      setState(() {
        final index = _notifications.indexWhere((n) => n['id'].toString() == notificationId.toString());
        if (index >= 0) {
          // Remove processing state
          _notifications[index]['processing'] = false;
          
          // If API call was successful, mark as read
          if (response['status']) {
            _notifications[index]['is_read'] = true;
          }
        }
      });
      
      if (response['status']) {
        // Update in notification service
        _notificationService.markNotificationAsRead(id);
        
        // Show success message
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Notification marked as read'),
            backgroundColor: AppTheme.successColor,
            duration: Duration(seconds: 2),
          ),
        );
      } else {
        // Show error message
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['msg'] ?? 'Failed to mark notification as read'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    } catch (e) {
      // Remove processing state
      setState(() {
        final index = _notifications.indexWhere((n) => n['id'].toString() == notificationId.toString());
        if (index >= 0) {
          _notifications[index]['processing'] = false;
        }
      });
      
      debugPrint('Error marking notification as read: ${e.toString()}');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error marking notification as read: ${e.toString()}'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
    }
  }

  Future<void> _markAllAsRead() async {
    // Get unread notification IDs
    final unreadNotifications = _notifications
        .where((n) => n['is_read'] == false || n['is_read'] == 0)
        .toList();

    if (unreadNotifications.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('No unread notifications'),
          backgroundColor: AppTheme.infoColor,
        ),
      );
      return;
    }

    // Show confirmation dialog
    final shouldMarkAll = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Mark All as Read', style: AppTheme.titleStyle),
        content: Text(
          'Are you sure you want to mark all notifications as read?',
          style: AppTheme.bodyStyle,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: Text('Mark All Read'),
          ),
        ],
      ),
    ) ?? false;

    if (!shouldMarkAll) return;

    setState(() {
      _isLoading = true;
    });

    int successCount = 0;

    // Process each notification
    for (final notification in unreadNotifications) {
      try {
        // Get ID and ensure it's an int
        final idRaw = notification['id'];
        if (idRaw == null) continue;

        final id = int.tryParse(idRaw.toString()) ?? 0;
        if (id <= 0) continue;

        final response = await _apiService.markNotificationAsRead(id);
        if (response['status']) {
          successCount++;
          _notificationService.markNotificationAsRead(id);
        }
      } catch (e) {
        debugPrint('Error marking notification as read: ${e.toString()}');
      }
    }

    // Reload notifications to reflect changes
    await _loadNotifications();
    
    // Show result message
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Marked $successCount notifications as read'),
        backgroundColor: AppTheme.successColor,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: RefreshIndicator(
        onRefresh: _loadNotifications,
        child: _isLoading
            ? Center(child: CircularProgressIndicator())
            : _errorMessage.isNotEmpty
                ? _buildErrorView()
                : _buildNotificationList(),
      ),
      floatingActionButton: _hasUnreadNotifications()
      ? FloatingActionButton(
        onPressed: _markAllAsRead,
        backgroundColor: AppTheme.primaryColor,
        child: Icon(Icons.done_all),
        tooltip: 'Mark all as read',
      )
      : null,
    );
  }

  bool _hasUnreadNotifications() {
    return _notifications.any((n) => n['is_read'] == false || n['is_read'] == 0);
  }

  Widget _buildErrorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.error_outline,
              size: 70,
              color: AppTheme.errorColor,
            ),
            SizedBox(height: AppTheme.spacingMd),
            Text('Error loading notifications', style: AppTheme.titleStyle),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              _errorMessage,
              style: AppTheme.bodyStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingLg),
            ElevatedButton(
              onPressed: _loadNotifications,
              child: Text('Try Again'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNotificationList() {
    if (_notifications.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.notifications_none,
              size: 70,
              color: AppTheme.textSecondaryColor,
            ),
            SizedBox(height: AppTheme.spacingMd),
            Text(
              'No notifications yet',
              style: AppTheme.titleStyle,
            ),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              'New notifications will appear here',
              style: AppTheme.bodyStyle.copyWith(
                color: AppTheme.textSecondaryColor,
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: EdgeInsets.all(AppTheme.spacingMd),
      itemCount: _notifications.length,
      itemBuilder: (context, index) {
        final notification = _notifications[index];
        final isRead = notification['is_read'] == true || notification['is_read'] == 1;
        final isProcessing = notification['processing'] == true;
        final dateTime = DateTime.parse(notification['created_at']);
        final formattedDate = DateFormat('MMM dd, yyyy • hh:mm a').format(dateTime);
        final bool isTaskAssignment =
          notification['title'] == 'Task Assigned' ||
          notification['title'].toLowerCase().contains('assigned');

        return Card(
          margin: EdgeInsets.only(bottom: AppTheme.spacingMd),
          color: isRead ? null : AppTheme.primaryColor.withValues(alpha:0.05),
          child: InkWell(
            onTap: () {
              // If this is a task notification, navigate to the task
              if (notification['task_id'] != null) {
                _navigateToTaskDetails(notification['task_id']);
              }
            },
            child: Padding(
              padding: EdgeInsets.all(AppTheme.spacingMd),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      if (isTaskAssignment)
                        Icon(
                          Icons.assignment_outlined,
                          color: AppTheme.primaryColor,
                          size: 20,
                        ),
                      SizedBox(width: isTaskAssignment ? AppTheme.spacingSm : 0),
                      Expanded(
                        child: Text(
                          notification['title'] ?? 'Notification',
                          style: AppTheme.subtitleStyle.copyWith(
                            fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
                          ),
                        ),
                      ),
                      // Mark as read button
                      if (!isRead)
                        isProcessing
                            ? SizedBox(
                                width: 24,
                                height: 24,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                    AppTheme.primaryColor,
                                  ),
                                ),
                              )
                            : IconButton(
                                icon: Icon(
                                  Icons.done,
                                  color: AppTheme.primaryColor,
                                ),
                                onPressed: () {
                                  final notificationId = notification['id'];
                                  if (notificationId != null) {
                                    _markAsRead(int.tryParse(notificationId.toString()) ?? 0);
                                  } else {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text('Invalid notification ID'),
                                        backgroundColor: AppTheme.errorColor,
                                      ),
                                    );
                                  }
                                },
                                tooltip: 'Mark as read',
                                constraints: BoxConstraints(
                                  minWidth: 40,
                                  minHeight: 40,
                                ),
                                padding: EdgeInsets.zero,
                                iconSize: 20,
                              ),
                    ],
                  ),
                  SizedBox(height: AppTheme.spacingSm),
                  Text(
                    notification['message'] ?? '',
                    style: AppTheme.bodyStyle,
                  ),
                  SizedBox(height: AppTheme.spacingMd),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        formattedDate,
                        style: AppTheme.captionStyle.copyWith(
                          color: AppTheme.textSecondaryColor,
                        ),
                      ),
                      // Status indicator
                      if (isRead)
                        Text(
                          'Read',
                          style: AppTheme.captionStyle.copyWith(
                            color: AppTheme.textSecondaryColor,
                          ),
                        )
                      else
                        Container(
                          padding: EdgeInsets.symmetric(
                            horizontal: AppTheme.spacingSm,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: AppTheme.primaryColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(AppTheme.borderRadiusSm),
                          ),
                          child: Text(
                            'New',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                              color: AppTheme.primaryColor,
                            ),
                          ),
                        ),
                    ],
                  ),
                if (notification['task_id'] != null)
                  Padding(
                    padding: EdgeInsets.only(top: AppTheme.spacingMd),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.end,
                      children: [
                        TextButton(
                          onPressed: () {
                            _navigateToTaskDetails(notification['task_id']);
                          },
                          child: Text(
                            'View Task',
                            style: TextStyle(
                              color: AppTheme.primaryColor,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  void dispose() {
    _ActivityPageStates.remove(widget);
    super.dispose();
  }

  void loadNotifications() {
    _loadNotifications();
  }

  void _navigateToTaskDetails(dynamic taskId) {
    if (taskId == null) return;

    int id;
    try {
      id = int.parse(taskId.toString());
    } catch (e) {
      print('Error parsing task ID: $e');
      return;
    }

    _apiService.viewTask(id).then((response) {
      if (response['status'] && response['data'] != null) {
        final task = Task.fromJson(response['data']);
        final userId = int.parse(widget.userData['user']['id'].toString());

        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => TasksDetailPage(
              task: task,
              currentUserId: userId,
              onTaskUpdated: () {},
            ),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load task details'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    }).catchError((error) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error loading task details: $error'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
    });
  }
}