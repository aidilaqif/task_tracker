require('dotenv').config();
const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const mysql = require('mysql2/promise');
const cors = require('cors');

// Configuration from environment variables
const PORT = process.env.PORT;
const NODE_ENV = process.env.NODE_ENV;
const POLL_INTERVAL = parseInt(process.env.NOTIFICATION_POLL_INTERVAL, 10);

// Database configuration
const dbConfig = {
  host: process.env.DB_HOST,
  port: parseInt(process.env.DB_PORT, 10),
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
};

// Initialize Express app
const app = express();
app.use(cors());
app.use(express.json());

// Create HTTP server
const server = http.createServer(app);

// Initialize Socket.IO
const io = socketIo(server, {
  cors: {
    origin: process.env.SOCKET_CORS_ORIGIN || "*",
    methods: ["GET", "POST"]
  },
  path: process.env.SOCKET_PATH || "/socket.io"
});

// Create MySQL connection pool
const pool = mysql.createPool(dbConfig);

// Test database connection on startup
async function testDatabaseConnection() {
  try {
    const connection = await pool.getConnection();
    console.log('Database connection successful');
    connection.release();
  } catch (error) {
    console.error('Database connection failed:', error.message);
    process.exit(1); // Exit if DB connection fails
  }
}

// Connected clients registry - maps user IDs to their socket IDs
const connectedUsers = new Map();

// Socket.IO connection handling
io.on('connection', (socket) => {
  console.log(`New client connected: ${socket.id}`);
  
  // Client authenticates with their user ID
  socket.on('authenticate', async (userId) => {
    try {
      // Ensure userId is an integer
      userId = parseInt(userId, 10);
      
      if (isNaN(userId) || userId <= 0) {
        socket.emit('error', { message: 'Invalid user ID' });
        return;
      }
      
      console.log(`User ${userId} authenticated on socket ${socket.id}`);
      
      // Store socket ID mapped to user ID (replacing any existing connection)
      connectedUsers.set(userId, socket.id);
      
      // Join user-specific room
      socket.join(`user-${userId}`);
      
      // Let client know they've been authenticated
      socket.emit('authenticated', { userId, timestamp: new Date().toISOString() });
      
      // Fetch and send any unread notifications to this user
      await sendUnreadNotifications(userId);
    } catch (error) {
      console.error(`Authentication error for socket ${socket.id}:`, error);
      socket.emit('error', { message: 'Authentication failed' });
    }
  });
  
  // Handle explicit notification acknowledgements from client
  socket.on('notification-received', (data) => {
    if (data && data.notificationId) {
      console.log(`Notification ${data.notificationId} received by client`);
    }
  });
  
  // Handle disconnection
  socket.on('disconnect', () => {
    // Remove from connected users
    for (const [userId, socketId] of connectedUsers.entries()) {
      if (socketId === socket.id) {
        connectedUsers.delete(userId);
        console.log(`User ${userId} disconnected`);
        break;
      }
    }
  });
  
  // Handle errors
  socket.on('error', (error) => {
    console.error(`Socket error for ${socket.id}:`, error);
  });
});

// Send unread notifications to a specific user
async function sendUnreadNotifications(userId) {
  try {
    const [rows] = await pool.query(`
      SELECT * FROM notifications 
      WHERE user_id = ? AND is_read = 0
      ORDER BY created_at DESC
    `, [userId]);
    
    if (rows.length > 0) {
      console.log(`Sending ${rows.length} unread notifications to user ${userId}`);
      io.to(`user-${userId}`).emit('unread-notifications', rows);
    } else {
      console.log(`No unread notifications for user ${userId}`);
    }
    
    return rows.length;
  } catch (error) {
    console.error(`Error fetching unread notifications for user ${userId}:`, error);
    return 0;
  }
}

// Function to poll database for new notifications
async function checkForNewNotifications() {
  try {
    // Look for notifications created in the last polling interval
    const [rows] = await pool.query(`
      SELECT * FROM notifications 
      WHERE created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
      AND is_read = 0
    `, [POLL_INTERVAL / 1000]); // Convert ms to seconds
    
    if (rows.length > 0) {
      console.log(`Found ${rows.length} new notifications`);
      
      // Group notifications by user_id for more efficient processing
      const notificationsByUser = rows.reduce((acc, notification) => {
        const userId = notification.user_id;
        if (!acc[userId]) {
          acc[userId] = [];
        }
        acc[userId].push(notification);
        return acc;
      }, {});
      
      // Process each user's notifications
      for (const [userId, notifications] of Object.entries(notificationsByUser)) {
        // Only send if the user is connected
        if (connectedUsers.has(parseInt(userId, 10))) {
          console.log(`Sending ${notifications.length} new notifications to user ${userId}`);
          io.to(`user-${userId}`).emit('new-notifications', notifications);
          
          // Also send individual notifications for better UX
          for (const notification of notifications) {
            io.to(`user-${userId}`).emit('new-notification', notification);
          }
        } else {
          console.log(`User ${userId} is not connected, skipping notification delivery`);
        }
      }
    }
  } catch (error) {
    console.error('Error checking for new notifications:', error);
  }
  
  // Schedule the next check after the poll interval
  setTimeout(checkForNewNotifications, POLL_INTERVAL);
}

// API routes

// Basic health check endpoint
app.get('/health', (req, res) => {
  res.status(200).json({ 
    status: 'ok',
    timestamp: new Date().toISOString(),
    environment: NODE_ENV,
    connections: connectedUsers.size
  });
});

// Get connected users (admin only in production)
app.get('/connected-users', (req, res) => {
  if (NODE_ENV === 'production') {
    // In production, require authentication for this endpoint
    const apiKey = req.headers['x-api-key'];
    if (apiKey !== process.env.ADMIN_API_KEY) {
      return res.status(401).json({ error: 'Unauthorized' });
    }
  }
  
  const users = Array.from(connectedUsers.keys());
  res.status(200).json({ 
    connectedUsers: users,
    count: users.length
  });
});

// Manually trigger a notification (useful for testing)
app.post('/trigger-notification', async (req, res) => {
  if (NODE_ENV === 'production') {
    // In production, require authentication
    const apiKey = req.headers['x-api-key'];
    if (apiKey !== process.env.ADMIN_API_KEY) {
      return res.status(401).json({ error: 'Unauthorized' });
    }
  }
  
  const { userId, title, message, taskId } = req.body;
  
  if (!userId || !title || !message) {
    return res.status(400).json({ error: 'Missing required fields' });
  }
  
  try {
    // Insert notification into database
    const [result] = await pool.query(`
      INSERT INTO notifications (user_id, task_id, title, message, is_read)
      VALUES (?, ?, ?, ?, 0)
    `, [userId, taskId || null, title, message]);
    
    // Get the inserted notification
    const [notifications] = await pool.query(`
      SELECT * FROM notifications WHERE id = ?
    `, [result.insertId]);
    
    if (notifications.length > 0) {
      const notification = notifications[0];
      
      // Send to user if connected
      if (connectedUsers.has(parseInt(userId, 10))) {
        io.to(`user-${userId}`).emit('new-notification', notification);
        res.status(201).json({ 
          success: true, 
          message: 'Notification created and sent',
          notification
        });
      } else {
        res.status(201).json({ 
          success: true, 
          message: 'Notification created but user not connected',
          notification
        });
      }
    } else {
      res.status(500).json({ error: 'Notification created but could not be retrieved' });
    }
  } catch (error) {
    console.error('Error triggering notification:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Handle 404 for any other routes
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
    await testDatabaseConnection();
    
    // Start HTTP server
    server.listen(PORT, () => {
      console.log(`Notification server running on port ${PORT} in ${NODE_ENV} mode`);
      
      // Start polling for new notifications
      checkForNewNotifications();
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
    pool.end();
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('SIGINT received, shutting down gracefully');
  server.close(() => {
    console.log('Server closed');
    pool.end();
    process.exit(0);
  });
});

// Start the application
startServer();