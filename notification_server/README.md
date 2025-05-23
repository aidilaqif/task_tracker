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

### Docker Requirements
- Docker Desktop installed and running
- Basic understanding of Docker concepts
- Access to host services (e.g., XAMPP MySQL)

## Installation

You can run the Notification Server using either traditional installation or Docker containers.

### Option 1: Traditional Installation

#### 1. Clone the repository
```bash
git clone <repository-url>
cd notification_server
```

#### 2. Install dependencies
```bash
npm install
```

#### 3. Create and configure the environment file
```bash
cp .env.example .env
```

#### 4. Update the `.env` file with your configuration

#### 5. Set up the database schema (see Database Setup section)

#### 6. Start the server
```bash
npm start
```

For development:
```bash
npm run dev
```

### Option 2: Docker Installation

Docker provides a consistent environment and simplifies deployment across different development setups.

#### 1. Clone the repository
```bash
git clone <repository-url>
cd notification_server
```

#### 2. Configure Environment for Docker

Copy and configure the environment file for Docker networking:

```bash
cp .env.example .env
```

Update `.env` file with Docker-specific configurations:

```env
# Server configuration
PORT=3000
NODE_ENV=development

# Database configuration for Docker
DB_HOST=host.docker.internal
DB_USER=root
DB_PASSWORD=
DB_NAME=task_tracker_db
DB_PORT=3307

# Socket.io configuration
SOCKET_CORS_ORIGIN=*
SOCKET_PATH=/socket.io

# Notification polling interval (in milliseconds)
NOTIFICATION_POLL_INTERVAL=5000

# CI4 backend connection (if using dockerized CI4 API)
API_BASE_URL=http://host.docker.internal:8080
```

#### 3. Set up the database schema (see Database Setup section)

#### 4. Build Docker Image

```bash
docker build -t notification-server .
```

#### 5. Run Docker Container

```bash
docker run -p 3000:3000 --name notification-server notification-server
```

#### 6. Access Your Notification Server

- **Server URL**: http://localhost:3000
- **Health Check**: http://localhost:3000/health (if implemented)
- **Socket.IO endpoint**: ws://localhost:3000

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

### Docker-Specific Configuration

When running in Docker, use these settings for connecting to host services:

```env
# For connecting to host MySQL (e.g., XAMPP)
DB_HOST=host.docker.internal

# For connecting to dockerized CI4 API
API_BASE_URL=http://host.docker.internal:8080
```

### Environment Switching

The configuration supports easy switching between environments:

**For Docker Development:**
```env
DB_HOST=host.docker.internal
API_BASE_URL=http://host.docker.internal:8080
```

**For Local Development:**
```env
DB_HOST=localhost
API_BASE_URL=http://localhost:8080
```

## Docker Configuration Details

### Dockerfile Highlights

The Docker configuration includes:

- **Base Image**: `node:18-alpine` for lightweight, secure container
- **Working Directory**: Set to `/app` for clean organization
- **Dependencies**: Uses `npm install` for all dependencies
- **Port**: Exposes port 3000 for the notification server
- **Startup**: Uses npm start script for application launch

### Docker Networking

When running in Docker:

- **host.docker.internal**: Used to connect to services running on your host machine (like XAMPP MySQL)
- **Port Mapping**: Container port 3000 maps to host port 3000
- **Service Communication**: Can communicate with other Docker containers or host services
- **CORS Configuration**: Allows connections from web applications running on different ports

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
├── Dockerfile        # Docker configuration
├── .dockerignore     # Docker ignore rules
└── handlers/         # Request and event handlers
    ├── notificationHandler.js  # HTTP route handlers
    └── socketHandler.js        # Socket.IO event handlers
```

## Integration with Other Services

### Running with Dockerized CI4 API

Both services can run in Docker containers and communicate with each other:

1. **Start CI4 API Container**:
```bash
docker run -p 8080:80 --name task-tracker task-tracker-app
```

2. **Start Notification Server Container**:
```bash
docker run -p 3000:3000 --name notification-server notification-server
```

3. **Service Communication**:
   - CI4 API (localhost:8080) → Notification Server (host.docker.internal:3000)
   - Notification Server (localhost:3000) → CI4 API (host.docker.internal:8080)
   - Both services → XAMPP MySQL (host.docker.internal:3307)

### Mixed Environment Support

The notification server supports various deployment scenarios:

- **Both services in Docker**: Container-to-container communication
- **Notification server in Docker, CI4 local**: Uses `host.docker.internal`
- **Both services local**: Traditional localhost communication
- **Hybrid cloud deployment**: Configure appropriate hostnames and ports

## Troubleshooting

### Common Issues

**Database Connection Problems:**
- Verify MySQL service is running
- Check database credentials in `.env`
- Test connection with `node db-test.js`

**Socket.IO Connection Issues:**
- Verify CORS settings allow your client domain
- Check if the server is accessible on the correct port
- Monitor client and server logs for connection errors

### Docker-Specific Issues

**Container won't start:**
```bash
# Check container logs
docker logs notification-server

# Check if port is already in use
docker ps
netstat -an | grep 3000
```

**Database connection fails from Docker:**
- Verify `host.docker.internal` is used for hostname
- Check if host MySQL service is accessible
- Test with: `docker exec -it notification-server node db-test.js`

**Service communication problems:**
- Verify network configurations between containers
- Check firewall settings on host machine
- Ensure environment variables are correctly set

### Useful Docker Commands

```bash
# View running containers
docker ps

# View container logs
docker logs notification-server

# Access container shell
docker exec -it notification-server sh

# Remove container
docker rm notification-server

# Remove image
docker rmi notification-server

# Rebuild without cache
docker build --no-cache -t notification-server .

# View container resource usage
docker stats notification-server
```

## Performance Considerations

### Docker Performance
- **Alpine base image**: Smaller image size and faster startup
- **Memory usage**: Node.js containers typically use less memory than full OS containers
- **Network latency**: Minimal overhead for Docker networking
- **Volume mounts**: Consider using volumes for persistent data if needed

### Scaling Considerations
- **Horizontal scaling**: Multiple container instances behind a load balancer
- **Database connection pooling**: Configured in `db.js` for efficient database usage
- **Socket.IO scaling**: Consider Redis adapter for multi-instance deployments

## Security Considerations

- **Container security**: Uses non-root user in Alpine image
- **Network isolation**: Containers run in isolated networks
- **Environment variables**: Sensitive data should use Docker secrets in production
- **CORS configuration**: Properly configured for your client applications
- **Database access**: Limited to necessary permissions only

## Development vs Production

### Development Setup
- Use `.env` with development database settings
- Enable debug logging and error reporting
- Connect to local services via `host.docker.internal`
- Hot reload for development (if configured)

### Production Considerations
- **Environment variables**: Use production-optimized settings
- **Logging**: Implement structured logging with external log aggregation
- **Monitoring**: Set up health checks and performance monitoring
- **Security**: Use Docker secrets for sensitive configuration
- **Reverse proxy**: Consider nginx for SSL termination and load balancing
- **Container orchestration**: Use Docker Compose or Kubernetes for production deployments

## Future Enhancements

- **Docker Compose**: Multi-container orchestration
- **Redis integration**: For scaling Socket.IO across multiple instances
- **Kubernetes manifests**: For cloud-native deployments
- **Health check endpoints**: More comprehensive monitoring
- **Metrics collection**: Integration with monitoring systems like Prometheus
- **Message queuing**: For handling high-volume notification scenarios
