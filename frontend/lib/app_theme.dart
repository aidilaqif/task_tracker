import 'package:flutter/material.dart';

/// AppTheme provides consistent styling constants for the Task Tracker application.
/// This helps maintain a uniform look and feel throughout the app.
class AppTheme {
  // Private constructor to prevent instantiation
  AppTheme._();
  
  // Colors
  static const Color primaryColor = Colors.deepPurple;
  static const Color secondaryColor = Colors.purpleAccent;
  static const Color backgroundColor = Colors.white;
  static const Color errorColor = Colors.red;
  static const Color successColor = Colors.green;
  static const Color warningColor = Colors.orange;
  static const Color textPrimaryColor = Colors.black87;
  static const Color textSecondaryColor = Colors.black54;
  static const Color dividerColor = Colors.grey;
  
  // Task Status Colors
  static const Color pendingColor = Colors.orange;
  static const Color inProgressColor = Colors.blue;
  static const Color completedColor = Colors.green;
  
  // Task Priority Colors
  static const Color lowPriorityColor = Colors.green;
  static const Color mediumPriorityColor = Colors.orange;
  static const Color highPriorityColor = Colors.red;
  
  // Background Colors for Priority Indicators
  static final Color lowPriorityBgColor = Colors.green.shade100;
  static final Color mediumPriorityBgColor = Colors.orange.shade100;
  static final Color highPriorityBgColor = Colors.red.shade100;
  
  // Text Styles
  static const TextStyle headingStyle = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.bold,
    color: textPrimaryColor,
  );
  
  static const TextStyle subheadingStyle = TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w500,
    color: textPrimaryColor,
  );
  
  static const TextStyle titleStyle = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.bold,
    color: textPrimaryColor,
  );
  
  static const TextStyle bodyStyle = TextStyle(
    fontSize: 14,
    color: textPrimaryColor,
  );
  
  static const TextStyle captionStyle = TextStyle(
    fontSize: 12,
    color: textSecondaryColor,
  );
  
  // Form Styling
  static const double inputBorderRadius = 8.0;
  static const EdgeInsets contentPadding = EdgeInsets.symmetric(
    vertical: 16.0,
    horizontal: 16.0,
  );
  
  // Button Styling
  static const double buttonHeight = 50.0;
  static const double buttonBorderRadius = 8.0;
  static const double buttonElevation = 1.0;
  
  // Card Styling
  static const double cardBorderRadius = 8.0;
  static const double cardElevation = 2.0;
  static const EdgeInsets cardPadding = EdgeInsets.all(12.0);
  
  // Spacing
  static const double spacingXs = 4.0;
  static const double spacingSm = 8.0;
  static const double spacingMd = 16.0;
  static const double spacingLg = 24.0;
  static const double spacingXl = 32.0;
  
  // Animation Durations
  static const Duration shortAnimationDuration = Duration(milliseconds: 200);
  static const Duration mediumAnimationDuration = Duration(milliseconds: 350);
  static const Duration longAnimationDuration = Duration(milliseconds: 500);

  // Get task priority color based on priority value
  static Color getPriorityColor(String priority) {
    switch (priority.toLowerCase()) {
      case 'high':
        return highPriorityColor;
      case 'medium':
        return mediumPriorityColor;
      case 'low':
        return lowPriorityColor;
      default:
        return mediumPriorityColor;
    }
  }
  
  // Get task priority background color based on priority value
  static Color getPriorityBgColor(String priority) {
    switch (priority.toLowerCase()) {
      case 'high':
        return highPriorityBgColor;
      case 'medium':
        return mediumPriorityBgColor;
      case 'low':
        return lowPriorityBgColor;
      default:
        return mediumPriorityBgColor;
    }
  }

  // Get task status color based on status value
  static Color getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'completed':
        return completedColor;
      case 'in-progress':
        return inProgressColor;
      case 'pending':
        return pendingColor;
      default:
        return pendingColor;
    }
  }
  
  // Generate app theme data 
  static ThemeData getAppTheme() {
    return ThemeData(
      colorScheme: ColorScheme.fromSeed(
        seedColor: primaryColor,
        primary: primaryColor,
        secondary: secondaryColor,
        error: errorColor,
        background: backgroundColor,
      ),
      inputDecorationTheme: InputDecorationTheme(
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(inputBorderRadius),
        ),
        contentPadding: contentPadding,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryColor,
          foregroundColor: Colors.white,
          minimumSize: Size(double.infinity, buttonHeight),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(buttonBorderRadius),
          ),
          elevation: buttonElevation,
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: primaryColor,
        ),
      ),
      cardTheme: CardTheme(
        elevation: cardElevation,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(cardBorderRadius),
        ),
      ),
    );
  }
}