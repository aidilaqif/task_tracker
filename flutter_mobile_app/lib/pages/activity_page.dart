import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/services/notification_service.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:intl/intl.dart';

class ActivityPage extends StatefulWidget {
  final Map<String, dynamic> userData;
  const ActivityPage({super.key, required this.userData});

  @override
  State<ActivityPage> createState() => _ActivityPageState();
}

class _ActivityPageState extends State<ActivityPage> {
  final ApiService _apiService = ApiService();
  final NotificationService _notificationService = NotificationService();
  final List<Map<String, dynamic>> _notifications = [];
  bool _isLoading = true;
  String _errorMessage = '';

  @override
  void initState() {
    super.initState();
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
      final response = await _apiService.markNotificationAsRead(notificationId);
      
      if (response['status']) {
        // Update notification in the list
        setState(() {
          final index = _notifications.indexWhere((n) => n['id'] == notificationId);
          if (index >= 0) {
            _notifications[index]['is_read'] = true;
          }
        });
      }
    } catch (e) {
      debugPrint('Error marking notification as read: ${e.toString()}');
    }
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
    );
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
        final dateTime = DateTime.parse(notification['created_at']);
        final formattedDate = DateFormat('MMM dd, yyyy • hh:mm a').format(dateTime);
        
        return Card(
          margin: EdgeInsets.only(bottom: AppTheme.spacingMd),
          color: isRead ? null : AppTheme.primaryColor.withOpacity(0.05),
          child: InkWell(
            onTap: () {
              if (!isRead) {
                _markAsRead(notification['id']);
              }
              
              // If this is a task notification, navigate to the task
              if (notification['task_id'] != null) {
                // TODO: Navigate to task details
              }
            },
            child: Padding(
              padding: EdgeInsets.all(AppTheme.spacingMd),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          notification['title'] ?? 'Notification',
                          style: AppTheme.subtitleStyle.copyWith(
                            fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
                          ),
                        ),
                      ),
                      if (!isRead)
                        Container(
                          width: 10,
                          height: 10,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                    ],
                  ),
                  SizedBox(height: AppTheme.spacingSm),
                  Text(
                    notification['message'] ?? '',
                    style: AppTheme.bodyStyle,
                  ),
                  SizedBox(height: AppTheme.spacingMd),
                  Text(
                    formattedDate,
                    style: AppTheme.captionStyle.copyWith(
                      color: AppTheme.textSecondaryColor,
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
    // Don't dispose the notification service here since it's a singleton
    super.dispose();
  }
}