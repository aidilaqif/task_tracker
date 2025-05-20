import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/task_model.dart';
import 'package:flutter_mobile_app/models/notification_group_model.dart';
import 'package:flutter_mobile_app/pages/task_detail/tasks_detail_page.dart';
import 'package:flutter_mobile_app/services/notification_service.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:flutter_mobile_app/pages/activity/empty_notifications_view.dart';
import 'package:flutter_mobile_app/pages/activity/notifications_error_view.dart';
import 'package:flutter_mobile_app/pages/activity/flat_notification_list.dart';
import 'package:flutter_mobile_app/pages/activity/grouped_notification_list.dart';

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
  final List<NotificationGroup> _notificationGroups = [];
  final Map<String, bool> _processingNotifications = {};
  bool _isLoading = true;
  String _errorMessage = '';
  bool _isGrouped = true;
  static _ActivityPageState? of(ActivityPage widget) {
    return _activityPageStates[widget];
  }

  static final Map<ActivityPage, _ActivityPageState> _activityPageStates = {};

  @override
  void initState() {
    super.initState();
    // Initialize local notifications
    final userId = int.parse(widget.userData['user']['id'].toString());
    _notificationService.init(userId);
    _activityPageStates[widget] = this;
    _loadNotifications();

    // Listen for new notifications
    _notificationService.notificationStream.listen((notification) {
      _addNotification(notification);
    });
  }

  // Group notifications by their type
  void _groupNotifications() {
    // Clear existing groups
    _notificationGroups.clear();

    // Create a map to hold notifications by type
    final Map<String, List<Map<String, dynamic>>> groupedNotifs = {};

    for (final notification in _notifications) {
      // Get notification type, default to 'general' if not set
      final type = notification['type'] ?? 'general';

      if (!groupedNotifs.containsKey(type)) {
        groupedNotifs[type] = [];
      }

      groupedNotifs[type]!.add(notification);
    }

    // Create NotificationGroup objects
    groupedNotifs.forEach((type, notifications) {
      _notificationGroups.add(
        NotificationGroup(
          type: type,
          displayName: NotificationGroup.getDisplayNameForType(type),
          icon: NotificationGroup.getIconForType(type),
          notifications: notifications,
        ),
      );
    });

    // Sort groups to show most common types first
    _notificationGroups.sort((a, b) {
      // First sort by notification count
      final countComparison = b.notifications.length.compareTo(
        a.notifications.length,
      );
      if (countComparison != 0) return countComparison;

      // Then by type priority (assignment first)
      final typeOrder = {
        'assignment': 0,
        'status': 1,
        'priority': 2,
        'progress': 3,
        'due_date': 4,
        'general': 5,
      };

      final aOrder = typeOrder[a.type.toLowerCase()] ?? 999;
      final bOrder = typeOrder[b.type.toLowerCase()] ?? 999;
      return aOrder.compareTo(bOrder);
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
          _processingNotifications.clear();

          // Add notifications from response
          if (response['data'] != null && response['data'] is List) {
            for (var notification in response['data']) {
              _notifications.add(notification);
            }
          }
          _groupNotifications();

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
    final existingIndex = _notifications.indexWhere(
      (n) => n['id'] == notification['id'],
    );

    setState(() {
      if (existingIndex >= 0) {
        // Update existing notification
        _notifications[existingIndex] = notification;
      } else {
        // Add new notification to the beginning of the list
        _notifications.insert(0, notification);
      }
      // Regroup notifications
      _groupNotifications();
    });
  }

  Future<void> _markAsRead(int notificationId) async {
    try {
      // Show loading indicator
      setState(() {
        // Mark as processing
        _processingNotifications[notificationId.toString()] = true;
      });

      // Convert to int
      final id = int.parse(notificationId.toString());

      final response = await _apiService.markNotificationAsRead(id);

      setState(() {
        // Remove processing state
        _processingNotifications[notificationId.toString()] = false;

        // If API call was successful, update notification
        if (response['status']) {
          final index = _notifications.indexWhere(
            (n) => n['id'].toString() == notificationId.toString(),
          );
          if (index >= 0) {
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
            content: Text(
              response['msg'] ?? 'Failed to mark notification as read',
            ),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    } catch (e) {
      // Remove processing state
      setState(() {
        _processingNotifications[notificationId.toString()] = false;
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
    final unreadNotifications =
        _notifications
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
    final shouldMarkAll =
        await showDialog<bool>(
          context: context,
          builder:
              (context) => AlertDialog(
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
        ) ??
        false;

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
        debugPrint('Error marking notification as read: $e');
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

  void _navigateToTaskDetails(dynamic taskId, [int? notificationId]) {
    if (taskId == null) return;

    int id;
    try {
      id = int.parse(taskId.toString());
    } catch (e) {
      print('Error parsing task ID: $e');
      return;
    }

    _apiService
        .viewTask(id)
        .then((response) {
          if (response['status'] && response['data'] != null) {
            final task = Task.fromJson(response['data']);
            final userId = int.parse(widget.userData['user']['id'].toString());

            // Mark notification as read if notification ID is provided
            if (notificationId != null) {
              _markAsRead(notificationId);
            }

            Navigator.push(
              context,
              MaterialPageRoute(
                builder:
                    (context) => TasksDetailPage(
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
        })
        .catchError((error) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Error loading task details: $error'),
              backgroundColor: AppTheme.errorColor,
            ),
          );
        });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: RefreshIndicator(
        onRefresh: _loadNotifications,
        child: Column(
          children: [
            Padding(
              padding: EdgeInsets.fromLTRB(
                AppTheme.spacingMd,
                AppTheme.spacingMd,
                AppTheme.spacingMd,
                0,
              ),
              child: Row(
                children: [
                  Text('Notifications', style: AppTheme.titleStyle),
                  Spacer(),
                  // Toggle switch between grouped and list view
                  Row(
                    children: [
                      Text('Group by type', style: AppTheme.captionStyle),
                      Switch(
                        value: _isGrouped,
                        onChanged: (value) {
                          setState(() {
                            _isGrouped = value;
                          });
                        },
                        activeColor: AppTheme.primaryColor,
                      ),
                    ],
                  ),
                ],
              ),
            ),
            Expanded(child: _buildContent()),
          ],
        ),
      ),
      floatingActionButton:
          _hasUnreadNotifications()
              ? FloatingActionButton(
                onPressed: _markAllAsRead,
                backgroundColor: AppTheme.primaryColor,
                tooltip: 'Mark all as read',
                child: Icon(Icons.done_all),
              )
              : null,
    );
  }

  Widget _buildContent() {
    if (_isLoading) {
      return Center(child: CircularProgressIndicator());
    }

    if (_errorMessage.isNotEmpty) {
      return NotificationsErrorView(
        errorMessage: _errorMessage,
        onRetry: _loadNotifications,
      );
    }

    if (_notifications.isEmpty) {
      return EmptyNotificationsView();
    }

    return _isGrouped
        ? GroupedNotificationList(
          notificationGroups: _notificationGroups,
          processingNotifications: _processingNotifications,
          onMarkAsRead: _markAsRead,
          onViewTask: _navigateToTaskDetails,
        )
        : FlatNotificationList(
          notifications: _notifications,
          processingNotifications: _processingNotifications,
          onMarkAsRead: _markAsRead,
          onViewTask: _navigateToTaskDetails,
        );
  }

  bool _hasUnreadNotifications() {
    return _notifications.any(
      (n) => n['is_read'] == false || n['is_read'] == 0,
    );
  }

  @override
  void dispose() {
    _activityPageStates.remove(widget);
    super.dispose();
  }

  void loadNotifications() {
    _loadNotifications();
  }
}
