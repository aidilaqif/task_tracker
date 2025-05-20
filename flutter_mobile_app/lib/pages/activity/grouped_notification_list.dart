import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/notification_group_model.dart';
import 'package:flutter_mobile_app/pages/activity/notification_group.dart';

class GroupedNotificationList extends StatelessWidget {
  final List<NotificationGroup> notificationGroups;
  final Map<String, bool> processingNotifications;
  final Function(int) onMarkAsRead;
  final Function(dynamic, int?) onViewTask;

  const GroupedNotificationList({
    super.key,
    required this.notificationGroups,
    required this.processingNotifications,
    required this.onMarkAsRead,
    required this.onViewTask,
  });

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: EdgeInsets.all(AppTheme.spacingMd),
      itemCount: notificationGroups.length,
      itemBuilder: (context, index) {
        final group = notificationGroups[index];
        return NotificationGroupWidget(
          group: group,
          processingNotifications: processingNotifications,
          onMarkAsRead: onMarkAsRead,
          onViewTask: onViewTask,
        );
      },
    );
  }
}
