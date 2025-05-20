import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/models/notification_group_model.dart';
import 'package:intl/intl.dart';

class FlatNotificationList extends StatelessWidget {
  final List<Map<String, dynamic>> notifications;
  final Map<String, bool> processingNotifications;
  final Function(int) onMarkAsRead;
  final Function(dynamic, int?) onViewTask;

  const FlatNotificationList({
    super.key,
    required this.notifications,
    required this.processingNotifications,
    required this.onMarkAsRead,
    required this.onViewTask,
  });

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: EdgeInsets.all(AppTheme.spacingMd),
      itemCount: notifications.length,
      itemBuilder: (context, index) {
        final notification = notifications[index];
        final isRead =
            notification['is_read'] == true || notification['is_read'] == 1;
        final isProcessing =
            processingNotifications[notification['id'].toString()] ?? false;
        final dateTime = DateTime.parse(notification['created_at']);
        final formattedDate = DateFormat(
          'MMM dd, yyyy • hh:mm a',
        ).format(dateTime);

        return Card(
          margin: EdgeInsets.only(bottom: AppTheme.spacingMd),
          color: isRead ? null : AppTheme.primaryColor.withValues(alpha: 0.05),
          child: InkWell(
            onTap: () {
              if (notification['task_id'] != null) {
                onViewTask(
                  notification['task_id'],
                  int.tryParse(notification['id'].toString()),
                );
              }
            },
            child: Padding(
              padding: EdgeInsets.all(AppTheme.spacingMd),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        NotificationGroup.getIconForType(
                          notification['type'] ?? 'general',
                        ),
                        color: AppTheme.primaryColor,
                        size: 20,
                      ),
                      SizedBox(width: AppTheme.spacingSm),
                      Expanded(
                        child: Text(
                          notification['title'] ?? 'Notification',
                          style: AppTheme.subtitleStyle.copyWith(
                            fontWeight:
                                isRead ? FontWeight.normal : FontWeight.bold,
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
                                  onMarkAsRead(
                                    int.tryParse(notificationId.toString()) ??
                                        0,
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
                            color: AppTheme.primaryColor.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(
                              AppTheme.borderRadiusSm,
                            ),
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
                              onViewTask(
                                notification['task_id'],
                                int.tryParse(notification['id'].toString()),
                              );
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
}
