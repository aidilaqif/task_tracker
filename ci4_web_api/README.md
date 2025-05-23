# Task Tracker - Team and Task Management Application

Task Tracker is a comprehensive team and task management web application built with CodeIgniter 4. It provides a robust system for team organization, task assignment, progress tracking, and real-time notifications.

## Features

- **User Management**
  - Role-based access control (Admin/User)
  - User registration and authentication
  - Profile management

- **Team Management**
  - Create and manage teams
  - Add/remove team members
  - Team performance metrics
  - Team completion rates visualization

- **Task Management**
  - Create, assign, and track tasks
  - Priority levels (High, Medium, Low)
  - Multiple status options (Pending, In-Progress, Completed, Request-Extension)
  - Progress tracking with percentage completion
  - Due date management

- **Real-time Notifications**
  - Task assignments
  - Status updates
  - Priority changes
  - Progress updates
  - Due date modifications

- **Dashboard & Analytics**
  - Task status overview
  - Priority distribution
  - Overdue tasks monitoring
  - Upcoming deadlines
  - Team performance metrics

- **Responsive Design**
  - Works on desktop and mobile devices
  - Bottom navigation on mobile for easy access

## Technology Stack

- **Backend**: PHP 8.1+, CodeIgniter 4
- **Frontend**: HTML5, CSS3, JavaScript
- **Database**: MySQL/MariaDB
- **Real-time Communication**: Socket.IO, WebSockets
- **Authentication**: Session-based authentication

## System Requirements

- PHP version 8.1 or higher
- Required PHP extensions:
  - intl
  - mbstring
  - json (enabled by default)
  - mysqlnd (if using MySQL)
  - libcurl (if using HTTP\CURLRequest library)
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- Node.js and npm (for the notification server)

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/task-tracker.git
cd ci4_web_api
```

### 2. Install Dependencies

```bash
composer install
npm install  # For notification server
```

### 3. Configure Environment

Copy the environment template file and modify it for your setup:

```bash
cp env .env
```

Edit `.env` file to configure:
- Base URL (`app.baseURL`)
- Database settings (`database.*`)
- Notification server URL (`NOTIFICATION_SERVER_URL`)

### 4. Setup Database

```bash
php spark migrate
php spark db:seed InitialSeeder  # Create default admin user
```

### 5. Start Notification Server

```bash
cd notification-server
npm start
```

### 6. Run the Application

For development:
```bash
php spark serve
```

For production, configure your web server to point to the `public` folder.

## Project Structure

```
ci4_web_api/
├── app/                  # Application code
│   ├── Config/           # Configuration files
│   ├── Controllers/      # Controllers
│   ├── Models/           # Data models
│   └── Views/            # UI templates
├── public/               # Publicly accessible files
│   ├── assets/           # CSS, JS, images
│   └── index.php         # Entry point
├── notification-server/  # Real-time notification server
└── vendor/               # Composer dependencies
```

## Key Components

### Models

- **UsersModel**: User account management
- **TeamModel**: Team creation and management
- **TasksModel**: Task tracking and assignment
- **NotificationsModel**: User notifications

### Controllers

- **AuthController**: Authentication and session management
- **UsersController**: User operations
- **TeamController**: Team operations
- **TasksController**: Task management
- **NotificationsController**: Notification handling
- **WebUIController**: Web interface views

### Views

- Dashboard, Team, Task, User Management, and Notification pages
- Modal components for creating/editing items
- Error pages and form templates

## API Endpoints

The application includes a RESTful API for integration with other systems:

### Users
- `GET /users` - Get all users
- `POST /users/add` - Create a new user
- `PUT /users/{id}` - Update a user
- `DELETE /users/{id}` - Delete a user

### Teams
- `GET /teams` - Get all teams
- `GET /teams/with-count` - Get teams with member counts
- `GET /teams/{id}/members` - Get team members
- `POST /teams` - Create a new team
- `PUT /teams/{id}` - Update a team

### Tasks
- `GET /tasks` - Get all tasks
- `POST /tasks/add` - Create a new task
- `PUT /tasks/edit/{id}` - Update a task
- `PUT /tasks/status/{id}` - Update task status
- `PUT /tasks/progress/{id}` - Update task progress

### Notifications
- `GET /admin/notifications` - Get admin notifications
- `POST /admin/notifications/mark-all-read` - Mark all notifications as read

## Configuration

### Environment Variables

- `NOTIFICATION_SERVER_URL` - URL for the notification server (default: http://localhost:3000)
- Database configuration (`database.*` variables)
- Email configuration (for password resets, notifications)

## Security Considerations

- Authentication is enforced through filters
- Form validation for all inputs
- CSRF protection enabled
- Session timeout handling
- XSS protection through output escaping

---

## Screenshots

[Add screenshots of key pages here]
