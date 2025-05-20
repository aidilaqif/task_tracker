import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';

class CompletionProgress extends StatelessWidget {
  final double completionRate;
  const CompletionProgress({super.key, required this.completionRate});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: AppTheme.spacingSm),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.trending_up, size: 20, color: AppTheme.primaryColor),
              SizedBox(width: AppTheme.spacingMd),
              Text('Completion Rate', style: AppTheme.subtitleStyle),
              Spacer(),
              Text(
                '${completionRate.toStringAsFixed(1)}%',
                style: AppTheme.subtitleStyle.copyWith(
                  color: AppTheme.primaryColor,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          SizedBox(height: AppTheme.spacingSm),
          ClipRRect(
            borderRadius: BorderRadius.circular(AppTheme.borderRadiusSm),
            child: LinearProgressIndicator(
              value: completionRate / 100,
              backgroundColor: AppTheme.dividerColor,
              valueColor: AlwaysStoppedAnimation<Color>(
                AppTheme.getProgressColor(completionRate.toInt()),
              ),
              minHeight: 8,
            ),
          ),
        ],
      ),
    );
  }
}
