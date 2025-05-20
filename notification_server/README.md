# Notification Server

A real-time notification system built with Node.js, Express, Socket.IO, and MySQL. This server enables instant delivery of notifications to connected clients with fallback to database storage for offline users.

## Features

- **Real-time notifications** via Socket.IO
- **Persistent storage** of notifications in MySQL database
- **Auto-reconnect** and notification sync for clients
- **User authentication** for secure notification delivery
- **Task assignment notifications** with custom handlers
- **Notification type inference** based on content
- **Health check endpoints** for monitoring
- **Efficient polling** for new notifications

## Prerequisites

- Node.js (v18 or higher)
- MySQL server
- npm or yarn

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd notification_server
```

2. Install dependencies
```bash
npm install
```

3. Create and configure the environment file
```bash
cp .env.example .env
```

4. Update the `.env` file with your configuration

5. Set up the database schema (see Database Setup section)

6. Start the server
```bash
npm start
```

For development:
```bash
npm run dev
```

## Database Setup

The notification server requires a MySQL database with the following schema:

```sql
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  task_id INT,
  title VARCHAR(255) NOT NULL,
  message TEXT,
  is_read BOOLEAN DEFAULT FALSE,
  type VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id),
  INDEX (is_read),
  INDEX (created_at)
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role VARCHAR(50)
  -- Other user fields as needed
);
```

You can test your database connection using:

```bash
node db-test.js
```

## Configuration

Configure the server by updating the `.env` file:

```
# Server configuration
PORT=3000
NODE_ENV=development

# Database configuration
DB_HOST=127.0.0.1
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=task_tracker_db
DB_PORT=3306

# Socket.io configuration
SOCKET_CORS_ORIGIN=*
SOCKET_PATH=/socket.io

# Notification polling interval (in milliseconds)
NOTIFICATION_POLL_INTERVAL=5000
```

## API Endpoints

### Send a Notification

```
POST /send-notification
```

Request body:
```json
{
  "notification_id": 123,
  "user_id": 456,
  "task_id": 789,
  "title": "New Task Update",
  "message": "The task status has been updated",
  "type": "status"
}
```

### Task Assignment Notification

```
POST /task-assigned
```

Request body:
```json
{
  "user_id": 456,
  "task_id": 789,
  "task_title": "Implement notification system"
}
```

### Health Check

```
GET /health
```

### Get Connected Users (Admin only)

```
GET /connected-users
```

Headers:
```
x-api-key: your_admin_api_key
```

## Socket.IO Events

### Client Events

- `authenticate`: Client sends their user ID to authenticate
  ```javascript
  socket.emit('authenticate', userId);
  ```

- `notification-received`: Client acknowledges receipt of a notification
  ```javascript
  socket.emit('notification-received', { notificationId: 123 });
  ```

### Server Events

- `authenticated`: Server confirms successful authentication
  ```javascript
  socket.on('authenticated', (data) => {
    console.log(`Authenticated as user ${data.userId}`);
  });
  ```

- `new-notification`: Server sends a new notification
  ```javascript
  socket.on('new-notification', (notification) => {
    console.log('New notification:', notification);
  });
  ```

- `unread-notifications`: Server sends batch of unread notifications
  ```javascript
  socket.on('unread-notifications', (notifications) => {
    console.log(`Received ${notifications.length} unread notifications`);
  });
  ```

- `error`: Server sends an error message
  ```javascript
  socket.on('error', (error) => {
    console.error('Socket error:', error);
  });
  ```

## Project Structure

```
notification_server/
├── .env.example      # Example environment configuration
├── config.js         # Server configuration
├── db.js             # Database connection and queries
├── db-test.js        # Database connection test utility
├── index.js          # Main application entry point
├── package.json      # Project metadata and dependencies
├── utils.js          # Utility functions
└── handlers/         # Request and event handlers
    ├── notificationHandler.js  # HTTP route handlers
    └── socketHandler.js        # Socket.IO event handlers
```
