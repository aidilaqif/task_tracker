import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class ProfileHeader extends StatelessWidget {
  final String name;
  final String email;
  final String role;

  const ProfileHeader({
    super.key,
    required this.name,
    required this.email,
    required this.role,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Avatar Cirlce
        CircleAvatar(
          radius: 50,
          backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
          child: Text(
            name.isNotEmpty ? name[0].toUpperCase() : '?',
            style: TextStyle(
              fontSize: 40,
              fontWeight: FontWeight.bold,
              color: AppTheme.primaryColor,
            ),
          ),
        ),
        SizedBox(height: AppTheme.spacingMd),
        // User Name
        Text(name, style: AppTheme.headlineStyle, textAlign: TextAlign.center),

        // Role Badge
        Container(
          padding: EdgeInsets.symmetric(
            horizontal: AppTheme.spacingMd,
            vertical: AppTheme.spacingSm,
          ),
          decoration: BoxDecoration(
            color:
                role.toLowerCase() == 'admin'
                    ? AppTheme.highPriorityBgColor
                    : AppTheme.lowPriorityBgColor,
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
          ),
          child: Text(
            role,
            style: TextStyle(
              color: AppTheme.textSecondaryColor,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        SizedBox(height: AppTheme.spacingMd),

        // Email
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.email_outlined,
              size: 16,
              color: AppTheme.textSecondaryColor,
            ),
            SizedBox(width: AppTheme.spacingMd),
            Text(
              email,
              style: AppTheme.bodyStyle.copyWith(
                color: AppTheme.textSecondaryColor,
              ),
            ),
          ],
        ),
      ],
    );
  }
}
