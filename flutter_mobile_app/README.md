# Task Tracker Mobile App

A Flutter mobile application for employees to track tasks, manage their work progress, view team information, and stay updated with notifications.

## Features

- **Authentication**
  - Secure login for team members
  - Session management
  - Role-based access (team member focus)

- **Task Management**
  - View assigned tasks
  - Filter tasks by status (pending, in-progress, completed, request-extension)
  - Update task status and progress
  - View task details including due dates, descriptions, and priority

- **Team Collaboration**
  - View team information
  - See team members and their roles
  - Identify your position within the team

- **Notifications**
  - Real-time notifications via Socket.IO
  - Grouped notifications by type
  - Mark notifications as read
  - View task details from notifications

- **Profile Management**
  - View user profile information
  - Track task completion metrics
  - Monitor performance statistics

## Project Structure

```
flutter_mobile_app/
├── lib/
│   ├── main.dart                 # Application entry point
│   ├── app_theme.dart            # App-wide theming
│   ├── custom_navigation_bar.dart # Main navigation
│   ├── models/                   # Data models
│   ├── pages/                    # UI screens
│   │   ├── activity/             # Notifications and activity
│   │   ├── login/                # Authentication
│   │   ├── profile/              # User profile
│   │   ├── task_detail/          # Task details
│   │   ├── tasks/                # Task listings
│   │   └── team/                 # Team information
│   ├── services/                 # API and services
│   │   ├── api_client.dart       # HTTP client
│   │   ├── api_routes.dart       # API endpoints
│   │   ├── api_services.dart     # Service aggregation
│   │   ├── api_services/         # Individual services
│   │   ├── local_notification_service.dart # Local notifications
│   │   └── socket_notification_service.dart # Real-time notifications
│   └── widgets/                  # Reusable UI components
```

## Setup Instructions

### Prerequisites

- Flutter SDK (3.7.2 or higher)
- Dart SDK (3.0.0 or higher)
- Android Studio / VS Code with Flutter plugins
- iOS development tools (for iOS deployment)

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/flutter_mobile_app.git
   cd flutter_mobile_app
   ```

2. Install dependencies:
   ```bash
   flutter pub get
   ```

3. Create a `.env` file in the project root with the following variables:
   ```
   API_BASE_URL=https://your-api-server.com/api
   NOTIFICATION_SERVER_URL=https://your-socket-server.com
   ```

### Running the App

```bash
# For development
flutter run

# For release build (Android)
flutter build apk --release

# For release build (iOS)
flutter build ios --release
```

## Dependencies

- **Network & API**
  - http: ^1.4.0 - HTTP requests
  - flutter_dotenv: ^5.2.1 - Environment variables
  - socket_io_client: ^3.1.2 - Real-time notifications

- **UI & Formatting**
  - intl: ^0.20.2 - Internationalization and date formatting

- **Local Storage & Notifications**
  - flutter_local_notifications: ^19.2.1 - Native notifications

## Backend Integration

This app is designed to work with a compatible REST API backend that provides:

- Authentication endpoints
- Task management
- Team information
- User profiles
- Notification handling

The backend should also include a Socket.IO server for real-time notifications.
