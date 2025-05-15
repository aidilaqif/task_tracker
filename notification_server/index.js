const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const config = require('./config');
const db = require('./db');
const socketHandler = require('./handlers/socketHandler');
const notificationHandler = require('./handlers/notificationHandler');

// Initialize Express app
const app = express();
app.use(cors());
app.use(express.json());

// Create HTTP server
const server = http.createServer(app);

// Initialize Socket.IO
const io = socketIo(server, config.socketIO);
const socketHandlerInstance = socketHandler.initSocketIO(io);

// Middleware to add socketHandler to request
app.use((req, res, next) => {
  req.socketHandler = socketHandlerInstance;
  next();
});

// Routes
app.post('/send-notification', notificationHandler.sendNotification);
app.post('/task-assigned', notificationHandler.taskAssigned);
app.get('/health', notificationHandler.healthCheck);
app.get('/connected-users', notificationHandler.getConnectedUsers);

// Handle 404
app.use((req, res) => {
  res.status(404).json({ error: 'Not found' });
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error('Unhandled error:', err);
  res.status(500).json({ error: 'Internal server error' });
});

// Start the server
async function startServer() {
  try {
    // Test database connection first
    const dbConnected = await db.testConnection();
    if (!dbConnected) {
      throw new Error('Failed to connect to database');
    }

    // Start HTTP server
    server.listen(config.port, () => {
      console.log(`Notification server running on port ${config.port} in ${config.environment} mode`);

      // Start polling for new notifications
      const pollInterval = config.pollInterval / 2;
      setInterval(() => socketHandlerInstance.checkForNewNotifications(), pollInterval);
    });
  } catch (error) {
    console.error('Failed to start server:', error);
    process.exit(1);
  }
}

// Handle process termination gracefully
process.on('SIGTERM', () => {
  console.log('SIGTERM received, shutting down gracefully');
  server.close(() => {
    console.log('Server closed');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('SIGINT received, shutting down gracefully');
  server.close(() => {
    console.log('Server closed');
    process.exit(0);
  });
});

// Start the application
startServer();