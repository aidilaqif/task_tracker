import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class TaskInfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color? iconColor;
  final List<Widget>? trailing;

  const TaskInfoRow({
    super.key,
    required this.icon,
    required this.label,
    this.iconColor,
    this.trailing,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 20, color: iconColor ?? AppTheme.primaryColor),
        SizedBox(width: AppTheme.spacingSm),
        Expanded(child: Text(label, style: AppTheme.subtitleStyle)),
        if (trailing != null) ...trailing!,
      ],
    );
  }
}
