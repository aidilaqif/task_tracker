import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class MemberCard extends StatelessWidget {
  final String name;
  final String email;
  final String role;
  final bool isCurrentUser;

  const MemberCard({
    super.key,
    required this.name,
    required this.email,
    required this.role,
    this.isCurrentUser = false,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.only(bottom: AppTheme.spacingMd),
      elevation: AppTheme.elevationSm,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
      ),
      child: Padding(
        padding: EdgeInsets.all(AppTheme.spacingMd),
        child: Row(
          children: [
            // Avatar with first letter of name
            CircleAvatar(
              radius: 25,
              backgroundColor: isCurrentUser
                  ? AppTheme.primaryColor
                  : AppTheme.secondaryColor.withOpacity(0.2),
              child: Text(
                name.isNotEmpty ? name[0].toUpperCase() : '?',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: isCurrentUser
                      ? AppTheme.textOnPrimaryColor
                      : AppTheme.textPrimaryColor,
                ),
              ),
            ),
            SizedBox(width: AppTheme.spacingMd),
            // Member info
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          name,
                          style: AppTheme.subtitleStyle,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (isCurrentUser)
                        Container(
                          padding: EdgeInsets.symmetric(
                            horizontal: AppTheme.spacingSm,
                            vertical: AppTheme.spacingXs,
                          ),
                          decoration: BoxDecoration(
                            color: AppTheme.primaryColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(AppTheme.borderRadiusSm),
                          ),
                          child: Text(
                            'You',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                              color: AppTheme.primaryColor,
                            ),
                          ),
                        ),
                    ],
                  ),
                  SizedBox(height: AppTheme.spacingXs),
                  Text(
                    email,
                    style: AppTheme.captionStyle.copyWith(
                      color: AppTheme.textSecondaryColor,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                  SizedBox(height: AppTheme.spacingXs),
                  // Role tag
                  Container(
                    padding: EdgeInsets.symmetric(
                      horizontal: AppTheme.spacingSm,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: role.toLowerCase() == 'admin'
                          ? AppTheme.highPriorityBgColor
                          : AppTheme.lowPriorityBgColor,
                      borderRadius: BorderRadius.circular(AppTheme.borderRadiusSm),
                    ),
                    child: Text(
                      role,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: role.toLowerCase() == 'admin'
                            ? AppTheme.highPriorityColor
                            : AppTheme.lowPriorityColor,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}