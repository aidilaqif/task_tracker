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

### Docker Requirements
- Docker Desktop installed and running
- Basic understanding of Docker concepts
- Access to host services (e.g., XAMPP MySQL)

## Installation

You can run the Task Tracker web application using either traditional installation or Docker containers.

### Option 1: Traditional Installation

#### 1. Clone the Repository

```bash
git clone https://github.com/your-username/task-tracker.git
cd ci4_web_api
```

#### 2. Install Dependencies

```bash
composer install
npm install  # For notification server
```

#### 3. Configure Environment

Copy the environment template file and modify it for your setup:

```bash
cp .env.example .env
```

Edit `.env` file to configure:
- Base URL (`app.baseURL`)
- Database settings (`database.*`)
- Notification server URL (`NOTIFICATION_SERVER_URL`)

#### 4. Setup Database

```bash
php spark migrate
php spark db:seed InitialSeeder  # Create default admin user
```

#### 5. Start Notification Server

```bash
cd notification-server
npm start
```

#### 6. Run the Application

For development:
```bash
php spark serve
```

For production, configure your web server to point to the `public` folder.

### Option 2: Docker Installation

Docker provides a consistent environment and simplifies deployment across different systems.

#### 1. Clone the Repository

```bash
git clone https://github.com/your-username/task-tracker.git
cd ci4_web_api
```

#### 2. Configure Environment for Docker

Copy and configure the environment file for Docker networking:

```bash
cp .env.example .env
```

Update `.env` file with Docker-specific configurations:

```env
# Environment
CI_ENVIRONMENT = development

# Notification server URL (Docker networking)
NOTIFICATION_SERVER_URL=http://host.docker.internal:3000

# Database Configuration for Docker
database.default.hostname = host.docker.internal
database.default.database = task_tracker_db
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.port = 3307
```

#### 3. Setup Database

Before running the Docker container, ensure your database is set up:

- Start your MySQL service (e.g., XAMPP)
- Create the database: `task_tracker_db`
- Run migrations and seeds (if running locally first):

```bash
# If you have PHP locally, run these before Docker
php spark migrate
php spark db:seed InitialSeeder
```

#### 4. Build Docker Image

```bash
docker build -t task-tracker-app .
```

#### 5. Run Docker Container

```bash
docker run -p 8080:80 --name task-tracker task-tracker-app
```

#### 6. Access Your Application

- **Application URL**: http://localhost:8080
- The containerized application connects to your host's MySQL service via `host.docker.internal`

#### 7. Start Notification Server

The notification server should be running separately (can also be dockerized):

```bash
cd ../notification_server
npm start
# Or run in Docker: docker run -p 3000:3000 --name notification-server notification-server
```

### Docker Configuration Details

#### Dockerfile Highlights

The Docker configuration includes:

- **Base Image**: `php:8.3-apache` for PHP 8.3 with Apache
- **PHP Extensions**: All required CodeIgniter extensions (intl, mbstring, mysqli, etc.)
- **Apache Configuration**: 
  - Document root set to `/public` directory
  - mod_rewrite enabled for URL routing
  - .htaccess support enabled
- **Composer**: Installed for dependency management
- **Permissions**: Proper file permissions for writable directories

#### Docker Networking

When running in Docker:

- **host.docker.internal**: Used to connect to services running on your host machine (like XAMPP MySQL)
- **Port Mapping**: Container port 80 maps to host port 8080
- **Service Communication**: Can communicate with other Docker containers or host services

#### Environment Switching

The configuration supports easy switching between environments:

**For Docker Development:**
```env
database.default.hostname = host.docker.internal
NOTIFICATION_SERVER_URL=http://host.docker.internal:3000
```

**For Local Development:**
```env
database.default.hostname = localhost
NOTIFICATION_SERVER_URL=http://localhost:3000
```

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
├── vendor/               # Composer dependencies
├── Dockerfile            # Docker configuration
├── .dockerignore         # Docker ignore rules
└── .env.example          # Environment template
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

### Docker-Specific Configuration

- `host.docker.internal` - Used for connecting to host services from within containers
- Port mappings and container networking considerations
- Volume mounts for persistent data (if needed)

## Security Considerations

- Authentication is enforced through filters
- Form validation for all inputs
- CSRF protection enabled
- Session timeout handling
- XSS protection through output escaping
- Docker security best practices (non-root users, minimal attack surface)

## Troubleshooting

### Common Issues

**Database Connection Problems:**
- Verify database service is running
- Check database credentials in `.env`
- For Docker: Ensure `host.docker.internal` is used for hostname

**Docker-Specific Issues:**
- **Container won't start**: Check Docker logs with `docker logs task-tracker`
- **Database connection fails**: Verify host services are accessible and firewall settings
- **Permission issues**: Ensure writable directories have correct permissions

**URL Routing Issues:**
- Verify Apache mod_rewrite is enabled
- Check .htaccess files are present and not ignored
- Ensure document root is set to `/public` directory

### Useful Docker Commands

```bash
# View running containers
docker ps

# View container logs
docker logs task-tracker

# Access container shell
docker exec -it task-tracker bash

# Remove container
docker rm task-tracker

# Remove image
docker rmi task-tracker-app

# Rebuild without cache
docker build --no-cache -t task-tracker-app .
```

## Development vs Production

### Development Setup
- Use `.env` with development database settings
- Enable debug mode and error reporting
- Connect to local services via `host.docker.internal`

### Production Considerations
- Use production-optimized `.env` settings
- Implement proper logging and monitoring
- Use Docker secrets for sensitive data
- Consider using Docker Compose for multi-container deployments
- Set up reverse proxy (nginx) for SSL termination

## Integration with Other Components

### With Notification Server
- Both can run in Docker containers
- Communication via `host.docker.internal` or container networking
- Shared database access for notification storage

### With Mobile Application
- Mobile app connects to web API via exposed ports
- Real-time notifications work through WebSocket connections
- Consistent API endpoints regardless of deployment method
