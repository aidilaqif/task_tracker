import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class StatusUpdateDialog extends StatelessWidget {
  final Function(String) onStatusSelected;

  const StatusUpdateDialog({super.key, required this.onStatusSelected});

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text('Update Status', style: AppTheme.titleStyle),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          ListTile(
            title: Text('In Progress'),
            onTap: () {
              Navigator.pop(context);
              onStatusSelected('in-progress');
            },
          ),
          ListTile(
            title: Text('Complete'),
            onTap: () {
              Navigator.pop(context);
              onStatusSelected('completed');
            },
          ),
          ListTile(
            title: Text('Request Extension'),
            onTap: () {
              Navigator.pop(context);
              onStatusSelected('request-extension');
            },
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: Text('Cancel'),
        ),
      ],
    );
  }
}
