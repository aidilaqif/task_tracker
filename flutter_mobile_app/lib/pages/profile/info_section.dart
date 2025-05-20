import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class InfoSection extends StatelessWidget {
  final String title;
  final List<Widget> items;

  const InfoSection({super.key, required this.title, required this.items});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: AppTheme.titleStyle),
        SizedBox(height: AppTheme.spacingMd),
        Card(
          elevation: AppTheme.elevationSm,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
          ),
          child: Padding(
            padding: EdgeInsets.all(AppTheme.spacingMd),
            child: Column(children: items),
          ),
        ),
      ],
    );
  }
}
