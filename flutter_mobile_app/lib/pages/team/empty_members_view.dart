import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class EmptyMembersView extends StatelessWidget {
  const EmptyMembersView({super.key});

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
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.people_outline,
              size: 60,
              color: AppTheme.textSecondaryColor,
            ),
            SizedBox(height: AppTheme.spacingMd),
            Text(
              'No Team Members',
              style: AppTheme.subtitleStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              'This team doesn\'t have any members yet',
              style: AppTheme.bodyStyle.copyWith(
                color: AppTheme.textSecondaryColor,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
