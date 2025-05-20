import 'package:flutter/material.dart';

class AppTheme {
  AppTheme._(); // Private constructor to prevent instantiation

  // Primary Colors (adapted from the web version with better contrast for mobile)
  static const Color primaryColor = Color(0xFF121212); // Deep purple
  static const Color primaryVariantColor = Color(0xFF3700B3); // Darker purple
  static const Color secondaryColor = Color(0xFF03DAC6); // Teal accent
  static const Color secondaryVariantColor = Color(0xFF018786); // Darker teal

  // Background Colors
  static const Color scaffoldBackgroundColor = Color(0xFFF8F9FA); // Light gray background
  static const Color cardBackgroundColor = Colors.white;
  static const Color appBarColor = primaryColor;

  // Text Colors
  static const Color textPrimaryColor = Color(0xFF212529); // Dark text
  static const Color textSecondaryColor = Color(0xFF6C757D); // Lighter text
  static const Color textOnPrimaryColor = Colors.white; // Text on primary color
  static const Color textOnSecondaryColor = Colors.black; // Text on secondary color

  // Icon Colors
  static const Color iconPrimaryColor = Color(0xFF212529); // Dark text
  static const Color iconSecondaryColor = Color(0xFF6C757D); // Lighter text
  static const Color iconOnPrimaryColor = Colors.white; // Text on primary color
  static const Color iconOnSecondaryColor = Colors.black; // Text on secondary color

  // Status Colors (consistent with the web app)
  static const Color pendingColor = Color(0xFFFFC107); // Amber
  static const Color inProgressColor = Color(0xFF17A2B8); // Cyan
  static const Color completedColor = Color(0xFF28A745); // Green
  static const Color requestExtensionColor = Color(0xFFDC3545); // Red

  // Priority Colors (consistent with the web app)
  static const Color lowPriorityColor = Color(0xFF28A745); // Green
  static const Color mediumPriorityColor = Color(0xFFFFC107); // Amber
  static const Color highPriorityColor = Color(0xFFDC3545); // Red

  // Background Colors for Priority Indicators
  static const Color lowPriorityBgColor = Color(0xFFD4EDDA); // Light green
  static const Color mediumPriorityBgColor = Color(0xFFFFF3CD); // Light amber
  static const Color highPriorityBgColor = Color(0xFFF8D7DA); // Light red

  // Form and Input Colors
  static const Color inputBorderColor = Color(0xFFCED4DA);
  static const Color inputFocusBorderColor = primaryColor;
  static const Color inputErrorBorderColor = Color(0xFFDC3545);

  // Alert and Notification Colors
  static const Color successColor = Color(0xFF28A745); // Green
  static const Color errorColor = Color(0xFFDC3545); // Red
  static const Color warningColor = Color(0xFFFFC107); // Amber
  static const Color infoColor = Color(0xFF17A2B8); // Cyan

  // Divider and Border Colors
  static const Color dividerColor = Color(0xFFDEE2E6);
  static const Color borderColor = Color(0xFFDEE2E6);

  // Shadow
  static const Color shadowColor = Color(0x40000000); // Black with alpha

  // Spacing
  static const double spacingXs = 4.0;
  static const double spacingSm = 8.0;
  static const double spacingMd = 16.0;
  static const double spacingLg = 24.0;
  static const double spacingXl = 32.0;

  // Border Radius
  static const double borderRadiusSm = 4.0;
  static const double borderRadiusMd = 8.0;
  static const double borderRadiusLg = 16.0;

  // Elevation
  static const double elevationSm = 1.0;
  static const double elevationMd = 3.0;
  static const double elevationLg = 6.0;

  // Text Styles
  static const TextStyle headlineStyle = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.bold,
    color: textPrimaryColor,
  );

  static const TextStyle titleStyle = TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.w600,
    color: textPrimaryColor,
  );

  static const TextStyle subtitleStyle = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w500,
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

  static const TextStyle buttonTextStyle = TextStyle(
    fontSize: 16,
    fontWeight: FontWeight.w500,
    color: textOnPrimaryColor,
  );

  // Status Text Styles
  static TextStyle getStatusTextStyle(String status) {
    Color textColor;
    Color backgroundColor;

    switch (status.toLowerCase()) {
      case 'pending':
        textColor = Color(0xFF856404);
        backgroundColor = Color(0xFFFFF3CD);
        break;
      case 'in-progress':
        textColor = Color(0xFF0C5460);
        backgroundColor = Color(0xFFD1ECF1);
        break;
      case 'completed':
        textColor = Color(0xFF155724);
        backgroundColor = Color(0xFFD4EDDA);
        break;
      case 'request-extension':
        textColor = Color(0xFF721C24);
        backgroundColor = Color(0xFFF8D7DA);
        break;
      default:
        textColor = textSecondaryColor;
        backgroundColor = Color(0xFFE9ECEF);
    }

    return TextStyle(
      color: textColor,
      fontSize: 14,
      fontWeight: FontWeight.w500,
      backgroundColor: backgroundColor,
    );
  }

  // Priority Text Styles
  static TextStyle getPriorityTextStyle(String priority) {
    Color color;

    switch (priority.toLowerCase()) {
      case 'high':
        color = highPriorityColor;
        break;
      case 'medium':
        color = mediumPriorityColor;
        break;
      case 'low':
        color = lowPriorityColor;
        break;
      default:
        color = textSecondaryColor;
    }

    return TextStyle(
      color: color,
      fontSize: 14,
      fontWeight: FontWeight.w500,
    );
  }

  // Progress Bar Color
  static Color getProgressColor(int progress) {
    if (progress < 30) {
      return requestExtensionColor; // Red for low progress
    } else if (progress < 70) {
      return pendingColor; // Amber for medium progress
    } else {
      return completedColor; // Green for high progress
    }
  }

  // Theme Data
  static ThemeData get lightTheme {
    return ThemeData(
      primaryColor: primaryColor,
      primaryColorDark: primaryVariantColor,
      colorScheme: ColorScheme.light(
        primary: primaryColor,
        primaryContainer: primaryVariantColor,
        secondary: secondaryColor,
        secondaryContainer: secondaryVariantColor,
        surface: cardBackgroundColor,
        background: scaffoldBackgroundColor,
        error: errorColor,
      ),
      scaffoldBackgroundColor: scaffoldBackgroundColor,
      appBarTheme: const AppBarTheme(
        backgroundColor: appBarColor,
        foregroundColor: textOnPrimaryColor,
        elevation: elevationSm,
      ),
      cardTheme: const CardTheme(
        color: cardBackgroundColor,
        elevation: elevationSm,
        margin: EdgeInsets.symmetric(vertical: spacingSm, horizontal: spacingSm),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.all(Radius.circular(borderRadiusMd)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: cardBackgroundColor,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: spacingMd,
          vertical: spacingMd,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(borderRadiusMd),
          borderSide: const BorderSide(color: inputBorderColor),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(borderRadiusMd),
          borderSide: const BorderSide(color: inputBorderColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(borderRadiusMd),
          borderSide: const BorderSide(color: inputFocusBorderColor, width: 2.0),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(borderRadiusMd),
          borderSide: const BorderSide(color: inputErrorBorderColor),
        ),
        labelStyle: const TextStyle(color: textSecondaryColor),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          foregroundColor: textOnPrimaryColor,
          backgroundColor: primaryColor,
          padding: const EdgeInsets.symmetric(
            horizontal: spacingMd,
            vertical: spacingMd,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(borderRadiusMd),
          ),
          elevation: elevationSm,
          textStyle: buttonTextStyle,
        ),
      ),
      textTheme: const TextTheme(
        headlineLarge: headlineStyle,
        titleLarge: titleStyle,
        titleMedium: subtitleStyle,
        bodyLarge: bodyStyle,
        bodyMedium: bodyStyle,
        labelLarge: buttonTextStyle,
        bodySmall: captionStyle,
      ),
      dividerTheme: const DividerThemeData(
        color: dividerColor,
        thickness: 1,
        space: spacingMd,
      ),
      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: primaryColor,
        linearTrackColor: Color(0xFFE9ECEF),
      ),
      checkboxTheme: CheckboxThemeData(
        fillColor: MaterialStateProperty.resolveWith<Color>((states) {
          if (states.contains(MaterialState.selected)) {
            return primaryColor;
          }
          return Colors.transparent;
        }),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(borderRadiusSm),
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: textPrimaryColor,
        contentTextStyle: const TextStyle(color: Colors.white),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(borderRadiusMd),
        ),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  // Function to get status-colored container decoration
  static BoxDecoration getStatusContainerDecoration(String status) {
    Color backgroundColor;
    
    switch (status.toLowerCase()) {
      case 'pending':
        backgroundColor = Color(0xFFFFF3CD); // Light amber
        break;
      case 'in-progress':
        backgroundColor = Color(0xFFD1ECF1); // Light cyan
        break;
      case 'completed':
        backgroundColor = Color(0xFFD4EDDA); // Light green
        break;
      case 'request-extension':
        backgroundColor = Color(0xFFF8D7DA); // Light red
        break;
      default:
        backgroundColor = Color(0xFFE9ECEF); // Light gray
    }
    
    return BoxDecoration(
      color: backgroundColor,
      borderRadius: BorderRadius.circular(borderRadiusMd),
      border: Border.all(
        color: backgroundColor.withOpacity(0.8),
        width: 1,
      ),
    );
  }

  // Function to get priority-colored container decoration
  static BoxDecoration getPriorityContainerDecoration(String priority) {
    Color backgroundColor;
    
    switch (priority.toLowerCase()) {
      case 'high':
        backgroundColor = highPriorityBgColor;
        break;
      case 'medium':
        backgroundColor = mediumPriorityBgColor;
        break;
      case 'low':
        backgroundColor = lowPriorityBgColor;
        break;
      default:
        backgroundColor = Color(0xFFE9ECEF); // Light gray
    }
    
    return BoxDecoration(
      color: backgroundColor,
      borderRadius: BorderRadius.circular(borderRadiusMd),
      border: Border.all(
        color: backgroundColor.withOpacity(0.8),
        width: 1,
      ),
    );
  }
}