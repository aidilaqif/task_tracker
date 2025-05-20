import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class MetricItem extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;

  const MetricItem({
    super.key,
    required this.label,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: AppTheme.spacingSm),
      child: Row(
        children: [
          Icon(icon, size: 20, color: AppTheme.primaryColor),
          SizedBox(width: AppTheme.spacingMd),
          Text(label, style: AppTheme.subtitleStyle),
          Spacer(),
          Text(
            value,
            style: AppTheme.subtitleStyle.copyWith(fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }
}
