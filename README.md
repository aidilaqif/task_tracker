# TaskTracker - Comprehensive Task Management System

TaskTracker is a complete task management solution that bridges the gap between management and team execution through specialized interfaces for different user roles. The system consists of three main components: a web application for managers, a mobile application for team members, and a real-time notification server.


## 🌟 System Overview

TaskTracker centralizes task assignment, monitoring, and reporting in a single cohesive platform:

| Component | Target Users | Technology | Status |
|-----------|--------------|------------|--------|
| Web Application | Managers/Admins | CodeIgniter 4, PHP | Completed |
| Mobile Application | Team Members | Flutter, Dart | Completed |
| Notification Server | System | Node.js, Socket.IO | Completed |

## 🏗️ Architecture

The system follows a client-server architecture with these components:

```
TaskTracker/
├── ci4_web_api/           # Web application backend + admin frontend
├── flutter_mobile_app/    # Mobile application for team members
└── notification_server/   # Real-time notification system
```

## 🖥️ Web Application (Manager Interface)

Built with CodeIgniter 4, the web application provides managers with comprehensive tools for team and task management.

### Key Features

- **User Management**
  - Role-based access control (Admin/User)
  - User authentication with secure sessions
  - User profiles and permissions

- **Team Management**
  - Create and organize teams
  - Add/remove team members
  - View team performance metrics
  - Track team completion rates

- **Task Administration**
  - Create and assign tasks with details
  - Define priority levels (High, Medium, Low)
  - Track task status (Pending, In-Progress, Completed, Request-Extension)
  - Monitor progress percentage
  - Set and manage deadlines

- **Dashboard & Analytics**
  - Task status overview
  - Priority distribution visualization
  - Overdue tasks monitoring
  - Upcoming deadlines tracking
  - Team performance comparison

## 📱 Mobile Application (Team Member Interface)

Built with Flutter, the mobile application provides team members with a streamlined interface for managing their assigned tasks.

### Key Features

- **Task Management**
  - View assigned tasks with details
  - Filter tasks by status
  - Update task status and progress
  - Request deadline extensions

- **Team Collaboration**
  - View team information
  - See team member profiles
  - Identify your role within the team

- **Real-time Notifications**
  - Receive instant alerts for new assignments
  - Get updates on task changes
  - Group notifications by type
  - Navigate directly to relevant tasks

- **Profile & Performance**
  - View personal task statistics
  - Track completion rate
  - Monitor workload distribution

## 🔔 Notification Server

Built with Node.js and Socket.IO, the notification server provides real-time communication between the web and mobile applications.

### Key Features

- **Real-time Delivery**
  - Instant push notifications
  - WebSocket communication
  - Fallback to database for offline users

- **Notification Types**
  - Task assignments
  - Status updates
  - Priority changes
  - Progress updates
  - Due date modifications

- **Administration**
  - Health monitoring
  - Connected user tracking
  - Notification delivery status

## 🛠️ Technology Stack

- **Backend**
  - Web API: PHP 8.1+, CodeIgniter 4
  - Notification Server: Node.js, Express, Socket.IO
  - Database: MySQL

- **Frontend**
  - Web: HTML5, CSS3, JavaScript
  - Mobile: Flutter, Dart

- **Communication**
  - RESTful APIs
  - WebSockets
  - Socket.IO

## 📋 System Requirements

### Web Application
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- Required PHP extensions: intl, mbstring, json, mysqlnd, libcurl

### Mobile Application
- Flutter SDK (3.7.2 or higher)
- Dart SDK (3.0.0 or higher)
- Android Studio / VS Code with Flutter plugins
- iOS development tools (for iOS deployment)

### Notification Server
- Node.js (v18 or higher)
- npm or yarn
- MySQL access

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/tasktracker.git
cd tasktracker
```

### 2. Set Up Web Application

```bash
cd ci4_web_api
composer install
cp env .env
# Configure .env with your database settings
php spark migrate
php spark db:seed InitialSeeder
```

### 3. Set Up Notification Server

```bash
cd ../notification_server
npm install
cp .env.example .env
# Configure .env with your settings
```

### 4. Set Up Mobile Application

```bash
cd ../flutter_mobile_app
flutter pub get
# Create .env file with your API_BASE_URL and NOTIFICATION_SERVER_URL
```

### 5. Start the Services

```bash
# Start web server (from ci4_web_api directory)
php spark serve

# Start notification server (from notification_server directory)
npm start

# Run mobile app (from flutter_mobile_app directory)
flutter run
```

## 📊 Database Schema

The system uses a consistent database schema across components:

```
┌───────────┐       ┌───────────┐       ┌───────────┐
│   users   │       │   teams   │       │   tasks   │
├───────────┤       ├───────────┤       ├───────────┤
│ id        │       │ id        │       │ id        │
│ name      │       │ name      │       │ user_id   │
│ email     │◄─────►│ description│◄─────►│ title     │
│ password  │       └───────────┘       │ description│
│ role      │                           │ due_date   │
│ team_id   │       ┌───────────┐       │ status     │
└───────────┘       │notifications│     │ priority   │
                    ├───────────┤       │ progress   │
                    │ id        │       └───────────┘
                    │ user_id   │
                    │ task_id   │
                    │ title     │
                    │ message   │
                    │ is_read   │
                    │ type      │
                    └───────────┘
```

## 🔒 Security Features

- Authentication and authorization for all components
- CSRF protection in web application
- Secure WebSocket connections
- Data validation across all inputs
- XSS protection through output escaping

## 🔜 Future Development

- Enhanced reporting and analytics
- Calendar integrations
- Time tracking features
- Document attachments
- Mobile offline mode
- Multi-language support


## Component Documentation

For detailed documentation on each component, please see:

- [Web Application Documentation](ci4_web_api/README.md)
- [Mobile Application Documentation](flutter_mobile_app/README.md)
- [Notification Server Documentation](notification_server/README.md)
