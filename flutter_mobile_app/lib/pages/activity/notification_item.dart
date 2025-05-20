import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:intl/intl.dart';

class NotificationItem extends StatelessWidget {
  final Map<String, dynamic> notification;
  final bool isProcessing;
  final Function(int) onMarkAsRead;
  final Function(dynamic, int?) onViewTask;

  const NotificationItem({
    super.key,
    required this.notification,
    required this.isProcessing,
    required this.onMarkAsRead,
    required this.onViewTask,
  });

  @override
  Widget build(BuildContext context) {
    final isRead =
        notification['is_read'] == true || notification['is_read'] == 1;
    final dateTime = DateTime.parse(notification['created_at']);
    final formattedDate = DateFormat('MMM dd, yyyy • hh:mm a').format(dateTime);

    return Padding(
      padding: EdgeInsets.all(AppTheme.spacingMd),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  notification['title'] ?? 'Notification',
                  style: AppTheme.bodyStyle.copyWith(
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
                      icon: Icon(Icons.done, color: AppTheme.primaryColor),
                      onPressed: () {
                        final notificationId = notification['id'];
                        if (notificationId != null) {
                          onMarkAsRead(
                            int.tryParse(notificationId.toString()) ?? 0,
                          );
                        }
                      },
                      tooltip: 'Mark as read',
                      constraints: BoxConstraints(minWidth: 40, minHeight: 40),
                      padding: EdgeInsets.zero,
                      iconSize: 20,
                    ),
            ],
          ),
          SizedBox(height: AppTheme.spacingSm),
          Text(
            notification['message'] ?? '',
            style: AppTheme.captionStyle.copyWith(
              color: AppTheme.textSecondaryColor,
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          SizedBox(height: AppTheme.spacingSm),
          Row(
            children: [
              Text(
                formattedDate,
                style: AppTheme.captionStyle.copyWith(
                  color: AppTheme.textSecondaryColor,
                  fontSize: 10,
                ),
              ),
              Spacer(),
              if (notification['task_id'] != null)
                TextButton(
                  onPressed: () {
                    onViewTask(
                      notification['task_id'],
                      int.tryParse(notification['id'].toString()),
                    );
                  },
                  style: TextButton.styleFrom(
                    padding: EdgeInsets.symmetric(
                      horizontal: AppTheme.spacingSm,
                      vertical: 0,
                    ),
                    minimumSize: Size(60, 24),
                  ),
                  child: Text(
                    'View Task',
                    style: TextStyle(
                      color: AppTheme.primaryColor,
                      fontSize: 12,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}
