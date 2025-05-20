import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/notification_group_model.dart';
import 'package:flutter_mobile_app/pages/activity/notification_item.dart';

class NotificationGroupWidget extends StatelessWidget {
  final NotificationGroup group;
  final Function(int) onMarkAsRead;
  final Function(dynamic, int?) onViewTask;
  final Map<String, bool> processingNotifications;

  const NotificationGroupWidget({
    super.key,
    required this.group,
    required this.onMarkAsRead,
    required this.onViewTask,
    required this.processingNotifications,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.only(bottom: AppTheme.spacingMd),
      child: ExpansionTile(
        initiallyExpanded: group.isExpanded,
        onExpansionChanged: (expanded) {
          group.isExpanded = expanded;
        },
        leading: CircleAvatar(
          backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
          child: Icon(group.icon, color: AppTheme.primaryColor, size: 20),
        ),
        title: Text(group.displayName, style: AppTheme.subtitleStyle),
        subtitle: Text(
          '${group.notifications.length} notification${group.notifications.length != 1 ? 's' : ''}',
          style: AppTheme.captionStyle,
        ),
        trailing:
            _getUnreadCountInGroup() > 0
                ? Container(
                  padding: EdgeInsets.symmetric(
                    horizontal: AppTheme.spacingSm,
                    vertical: 2,
                  ),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryColor,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    _getUnreadCountInGroup().toString(),
                    style: TextStyle(
                      color: AppTheme.textOnPrimaryColor,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                )
                : null,
        children: _buildNotificationItems(),
      ),
    );
  }

  int _getUnreadCountInGroup() {
    return group.notifications
        .where((n) => n['is_read'] == false || n['is_read'] == 0)
        .length;
  }

  List<Widget> _buildNotificationItems() {
    final List<Widget> items = [];

    for (int i = 0; i < group.notifications.length; i++) {
      final notification = group.notifications[i];

      items.add(
        NotificationItem(
          notification: notification,
          isProcessing:
              processingNotifications[notification['id'].toString()] ?? false,
          onMarkAsRead: onMarkAsRead,
          onViewTask: onViewTask,
        ),
      );

      // Add divider between items (except after the last one)
      if (i < group.notifications.length - 1) {
        items.add(Divider(height: AppTheme.spacingMd));
      }
    }

    return items;
  }
}
