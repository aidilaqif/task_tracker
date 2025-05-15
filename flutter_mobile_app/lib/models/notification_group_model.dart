import 'package:flutter/material.dart';

class NotificationGroup {
  final String type;
  final String displayName;
  final IconData icon;
  final List<Map<String, dynamic>> notifications;
  bool isExpanded;

  NotificationGroup({
    required this.type,
    required this.displayName,
    required this.icon,
    required this.notifications,
    this.isExpanded = false,
  });

  static IconData getIconForType(String type) {
    switch (type.toLowerCase()) {
      case 'assignment':
        return Icons.assignment;
      case 'status':
        return Icons.update;
      case 'priority':
        return Icons.flag;
      case 'progress':
        return Icons.trending_up;
      case 'due_date':
        return Icons.event;
      default:
        return Icons.notifications;
    }
  }

  static String getDisplayNameForType(String type) {
    switch (type.toLowerCase()) {
      case 'assignment':
        return 'Task Assignments';
      case 'status':
        return 'Status Updates';
      case 'priority':
        return 'Priority Changes';
      case 'progress':
        return 'Progress Updates';
      case 'due_date':
        return 'Due Date Changes';
      default:
        return 'General Updates';
    }
  }
}