import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class ConnectionStatus extends StatelessWidget {
  final String connectionStatus;

  const ConnectionStatus({super.key, required this.connectionStatus});

  @override
  Widget build(BuildContext context) {
    if (connectionStatus.isEmpty) {
      return SizedBox.shrink();
    }

    final bool isSuccess =
        connectionStatus.contains('successful') ||
        connectionStatus.contains('Connected');

    return Container(
      padding: EdgeInsets.all(AppTheme.spacingSm),
      decoration: BoxDecoration(
        color:
            isSuccess
                ? AppTheme.lowPriorityBgColor
                : AppTheme.highPriorityBgColor,
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
      ),
      child: Text(
        connectionStatus,
        style: TextStyle(
          color:
              isSuccess
                  ? AppTheme.lowPriorityColor
                  : AppTheme.highPriorityColor,
        ),
        textAlign: TextAlign.center,
      ),
    );
  }
}
