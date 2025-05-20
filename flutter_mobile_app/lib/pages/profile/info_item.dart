import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class InfoItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const InfoItem({
    super.key,
    required this.icon,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: AppTheme.spacingSm),
      child: Row(
        children: [
          Icon(icon, size: 20, color: AppTheme.primaryColor),
          SizedBox(width: AppTheme.spacingMd),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: AppTheme.captionStyle),
                Text(value, style: AppTheme.bodyStyle),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
