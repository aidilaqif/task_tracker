import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class TeamHeader extends StatelessWidget {
  final String name;
  final String description;

  const TeamHeader({super.key, required this.name, required this.description});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: AppTheme.elevationSm,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
      ),
      child: Padding(
        padding: EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Team Icon
            Row(
              children: [
                CircleAvatar(
                  radius: 30,
                  backgroundColor: AppTheme.primaryColor.withValues(alpha: 0.1),
                  child: Icon(
                    Icons.people,
                    size: 30,
                    color: AppTheme.primaryColor,
                  ),
                ),
                SizedBox(width: AppTheme.spacingMd),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: AppTheme.headlineStyle,
                        overflow: TextOverflow.ellipsis,
                      ),
                      SizedBox(height: AppTheme.spacingXs),
                      Text(
                        'Team',
                        style: AppTheme.captionStyle.copyWith(
                          color: AppTheme.textSecondaryColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            SizedBox(height: AppTheme.spacingMd),
            // Divider between team header and description
            Divider(height: 1, thickness: 1),
            SizedBox(height: AppTheme.spacingMd),
            // Team Description
            Text('Description', style: AppTheme.subtitleStyle),
            SizedBox(height: AppTheme.spacingSm),
            Text(description, style: AppTheme.bodyStyle),
          ],
        ),
      ),
    );
  }
}
