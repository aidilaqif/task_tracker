require('dotenv').config();
const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const mysql = require('mysql2/promise');
const cors = require('cors');

// Configuration from environment variables
const PORT = process.env.PORT;
const NODE_ENV = process.env.NODE_ENV;
const POLL_INTERVAL = parseInt(process.env.NOTIFICATION_POLL_INTERVAL, 10) || 5000;

// Database configuration
const dbConfig = {
  host: process.env.DB_HOST,
  port: parseInt(process.env.DB_PORT, 10),
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  waitForConnections: true,
  connectionLimit: 20,
  queueLimit: 0
};

// Track processed notification
const processedNotifications = new Set();
const MAX_TRACKED_NOTIFICATIONS = 1000;

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
    methods: ["GET", "POST"],
    credentials: true,
    allowedHeaders: ["*"]
  },
  path: process.env.SOCKET_PATH || "/socket.io",
  transports: ['websocket', 'polling']
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
        console.error(`Invalid user ID for authentication: ${userId}`);
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
      console.log(`Sent authentication confirmation to user ${userId}`);
      
      // Fetch and send any unread notifications to this user
      const notificationCount = await sendUnreadNotifications(userId);
      console.log(`Sent ${notificationCount} unread notifications to user ${userId}`);
    } catch (error) {
      console.error(`Authentication error for socket ${socket.id}:`, error);
      socket.emit('error', { message: 'Authentication failed: ' + error.message });
    }
  });
  
  // Improved notification received acknowledgement
  socket.on('notification-received', (data) => {
    if (data && data.notificationId) {
      console.log(`Client acknowledged receipt of notification ${data.notificationId}`);
    }
  });
  
  // Handle disconnection with better logging
  socket.on('disconnect', () => {
    // Remove from connected users
    for (const [userId, socketId] of connectedUsers.entries()) {
      if (socketId === socket.id) {
        console.log(`User ${userId} disconnected (socket ${socket.id})`);
        connectedUsers.delete(userId);
        break;
      }
    }
  });

  // Handle for check-past-due-tasks event
  socket.on('check-past-due-tasks', async (data) => {
        try {
          // Get the user ID from the socket connection
          let userId = null;
          for (const [id, socketId] of connectedUsers.entries()) {
            if (socketId === socket.id) {
              userId = id;
              break;
            }
          }

          if (!userId) {
            console.error('User not authenticated for check-past-due-tasks');
            return;
          }

          console.log(`Checking due soon tasks for user ${userId}`);

          // Call the API to check for tasks due soon for this specific user
          const response = await fetch(`${process.env.API_BASE_URL || 'http://localhost:8080'}/tasks/user/${userId}?status=pending`, {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          });

          if (response.ok) {
            const result = await response.json();

            // Filter tasks due within the next 24 hours
            const now = new Date();
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);

            const dueSoonTasks = (result.data || []).filter(task => {
              if (!task.due_date) return false;

              const dueDate = new Date(task.due_date);
              return dueDate > now && dueDate <= tomorrow;
            });

            console.log(`Found ${dueSoonTasks.length} tasks due soon for user ${userId}`);

            if (dueSoonTasks.length > 0) {
              // Emit the due soon tasks back to the client
              socket.emit('due-soon-tasks', dueSoonTasks);

              // Also send notifications for these tasks
              for (const task of dueSoonTasks) {
                try {
                  // Check if a notification already exists for this task
                  const [existingNotifications] = await pool.query(
                    'SELECT * FROM notifications WHERE user_id = ? AND task_id = ? AND title = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)',
                    [userId, task.id, 'Task Due Soon']
                  );

                  if (existingNotifications.length === 0) {
                    // Create a new notification
                    const [result] = await pool.query(
                      'INSERT INTO notifications (user_id, task_id, title, message, is_read) VALUES (?, ?, ?, ?, 0)',
                      [userId, task.id, 'Task Due Soon', `Task "${task.title}" is due soon. Please complete it before the deadline.`]
                    );

                    if (result.insertId) {
                      // Get the created notification
                      const [notifications] = await pool.query(
                        'SELECT * FROM notifications WHERE id = ?',
                        [result.insertId]
                      );

                      if (notifications.length > 0) {
                        // Send the notification to the client
                        socket.emit('new-notification', notifications[0]);
                        console.log(`Sent due soon notification for task ${task.id} to user ${userId}`);
                      }
                    }
                  } else {
                    console.log(`Due soon notification for task ${task.id} already exists`);
                  }
                } catch (error) {
                  console.error(`Error creating due soon notification for task ${task.id}:`, error);
                }
              }
            }
          } else {
            console.error('Failed to fetch tasks for due soon check:', await response.text());
          }
        } catch (error) {
          console.error('Error in check-past-due-tasks handler:', error);
        }
  });
});

function trackNotification(notificationId) {
  // Add to processed set
  processedNotifications.add(notificationId);

  // Keep the set size manageable
  if (processedNotifications.size > MAX_TRACKED_NOTIFICATIONS) {
    // Convert to array, keep only the most recent entries
    const notificationArray = Array.from(processedNotifications);
    const startIndex = notificationArray.length - Math.floor(MAX_TRACKED_NOTIFICATIONS / 2);
    processedNotifications.clear();

    for (let i = startIndex; i < notificationArray.length; i++) {
      processedNotifications.add(notificationArray[i]);
    }
  }
}

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
      
      // Send all unread notifications in a batch
      io.to(`user-${userId}`).emit('unread-notifications', rows);
      
      // Also send each notification individually for better handling
      for (const notification of rows) {
        console.log(`Sending individual notification ${notification.id} to user ${userId}`);
        io.to(`user-${userId}`).emit('new-notification', notification);
      }
      
      return rows.length;
    } else {
      console.log(`No unread notifications for user ${userId}`);
      return 0;
    }
  } catch (error) {
    console.error(`Error sending unread notifications to user ${userId}:`, error);
    return 0;
  }
}

// Function to poll database for new notifications
async function checkForNewNotifications() {
  try {
    // console.log('Checking for new notifications...');
    
    // Look for notifications created very recently
    const [rows] = await pool.query(`
      SELECT * FROM notifications 
      WHERE created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
      AND is_read = 0
    `, [POLL_INTERVAL / 1000 * 3]);

    if (rows.length > 0) {
      console.log(`Found ${rows.length} new notifications in this poll interval`);

      // Filter out notifications we've already processed
      const newNotifications = rows.filter(notification => 
        !processedNotifications.has(notification.id)
      );
      
      if (newNotifications.length === 0) {
        console.log('All notifications have already been processed');
      } else {
        console.log(`Processing ${newNotifications.length} new notifications`);
      
        // Track these notifications as processed to avoid duplicates
        newNotifications.forEach(notification => {
          trackNotification(notification.id);
        });

        // Group notifications by user_id for more efficient processing
        const notificationsByUser = {};
        for (const notification of newNotifications) {
          const userId = notification.user_id;
          if (!notificationsByUser[userId]) {
            notificationsByUser[userId] = [];
          }
          notificationsByUser[userId].push(notification);
        }

        // Process each user's notifications
        for (const [userId, notifications] of Object.entries(notificationsByUser)) {
          const userIdInt = parseInt(userId, 10);

          // Only send if the user is connected
          if (connectedUsers.has(userIdInt)) {
            const socketId = connectedUsers.get(userIdInt);
            console.log(`User ${userId} is connected with socket ${socketId}, sending ${notifications.length} notifications`);

            // Send each notification individually for immediate delivery
            for (const notification of notifications) {
              console.log(`Emitting notification ${notification.id} to user ${userId}`);

              // Send directly to the user's socket
              io.to(socketId).emit('new-notification', notification);

              // Also emit to the user's room as backup
              io.to(`user-${userIdInt}`).emit('new-notification', notification);
            }
          } else {
            console.log(`User ${userId} is not connected, notifications will be delivered on next connection`);
          }
        }
      }
    } // else {
    //   console.log('No new notifications found in this poll interval');
    // }
  } catch (error) {
    console.error('Error checking for new notifications:', error);
  }

  // Schedule the next check, using a shorter interval help catch updates faster
  setTimeout(checkForNewNotifications, POLL_INTERVAL / 2);
}

// Function to check for tasks due soon
async function checkDueSoonTasks() {
  try {
    console.log('Checking for tasks due soon...');

    // Create a date for tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowFormatted = tomorrow.toISOString().split('T')[0]; // Format as YYYY-MM-DD

    const response = await fetch(`${process.env.API_BASE_URL}/tasks/check-due-soon`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      }
    });

    if (response.ok) {
      const result = await response.json();
      console.log(`Checked for tasks due soon: ${result.data?.notifications_sent || 0}`);
    } else {
      console.error('Failed to check for tasks due soon:', await response.text());
    }
  } catch (error) {
    console.error('Error checking for tasks due soon:', error);
  }

  // Schedule the next check for tomorrow at 9AM
  const now = new Date();
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  tomorrow.setHours(9, 0, 0, 0); // Set to 9 AM

  const timeUntilNextCheck = tomorrow - now;
  console.log(`Next due date check scheduled for ${tomorrow.toLocaleString()}`);

  setTimeout(checkDueSoonTasks, timeUntilNextCheck);
}

// API route for direct notification delivery
app.post('/send-notification', async (req, res) => {
  try {
    // console.log('Received notification request:', req.body);
    
    const { notification_id, user_id, task_id, title, message } = req.body;
    
    if (!notification_id || !user_id) {
      console.error('Missing required fields in notification request:', req.body);
      return res.status(400).json({ error: 'Missing required fields' });
    }
    
    // Log the data received
    console.log(`Processing notification ${notification_id} for user ${user_id}, task ${task_id || 'N/A'}`);
    
    // Convert IDs to integers
    const notificationId = parseInt(notification_id, 10);
    const userId = parseInt(user_id, 10);
    
    // Create a notification object from the request data
    const notificationFromRequest = {
      id: notificationId,
      user_id: userId,
      task_id: task_id ? parseInt(task_id, 10) : null,
      title: title || '',
      message: message || '',
      is_read: false,
      created_at: new Date().toISOString()
    };
    
    // Try to verify the notification exists in the database, but don't fail if not found
    try {
      const [existingNotifications] = await pool.query(
        'SELECT * FROM notifications WHERE id = ?',
        [notificationId]
      );
      
      if (existingNotifications.length > 0) {
        console.log(`Found notification ${notificationId} in database`);
        notificationFromRequest.created_at = existingNotifications[0].created_at;
      } else {
        console.log(`Notification ${notificationId} not found in database - will use request data`);
      }
    } catch (dbError) {
      console.warn(`Database error checking notification ${notificationId}: ${dbError.message}`);
    }
    
    if (!global.processedNotifications) {
      global.processedNotifications = new Set();
    }
    global.processedNotifications.add(notificationId);
    
    // Check if user is connected
    const isUserConnected = connectedUsers.has(userId);
    console.log(`User ${userId} connected: ${isUserConnected}`);

    if (isUserConnected) {
      // Get socket ID
      const socketId = connectedUsers.get(userId);
      console.log(`Sending real-time notification ${notificationId} to user ${userId} on socket ${socketId}`);

      // Send via direct socket connection first
      const socket = io.sockets.sockets.get(socketId);
      if (socket) {
        socket.emit('new-notification', notificationFromRequest);
        console.log(`Emitted notification directly to socket ${socketId}`);
      } else {
        console.log(`Socket ${socketId} not found, falling back to room emission`);
      }

      // Broadcast to the user's room as backup
      io.to(`user-${userId}`).emit('new-notification', notificationFromRequest);
      console.log(`Broadcasted notification to room user-${userId}`);

      res.status(200).json({
        success: true,
        message: 'Notification sent in real-time',
        notification_id: notificationId,
        connected: true
      });
    } else {
      console.log(`User ${userId} not connected, notification will be delivered on next connection`);
      res.status(200).json({
        success: true,
        message: 'Notification saved but user not connected',
        notification_id: notificationId,
        connected: false
      });
    }
  } catch (error) {
    console.error('Error processing notification request:', error);
    res.status(500).json({ error: 'Internal server error: ' + error.message });
  }
});

// Special route for task assignment notifications
app.post('/task-assigned', async (req, res) => {
  try {
    console.log('Task assignment notification request received:', req.body);
    const { user_id, task_id, task_title } = req.body;
    
    if (!user_id || !task_id || !task_title) {
      console.error('Missing required fields in task assignment request:', req.body);
      return res.status(400).json({ error: 'Missing required fields' });
    }
    
    // Insert notification into database
    const [result] = await pool.query(`
      INSERT INTO notifications (user_id, task_id, title, message, is_read)
      VALUES (?, ?, ?, ?, 0)
    `, [user_id, task_id, 'Task Assigned', `You have been assigned to task: ${task_title}`]);
    
    // Get the inserted notification
    const [notifications] = await pool.query(`
      SELECT * FROM notifications WHERE id = ?
    `, [result.insertId]);
    
    if (notifications.length > 0) {
      const notification = notifications[0];
      const userId = parseInt(user_id, 10);
      
      // Immediately try to send the notification
      if (connectedUsers.has(userId)) {
        console.log(`Sending immediate task assignment notification to user ${userId}`);
        
        // Get the socket ID
        const socketId = connectedUsers.get(userId);
        
        // Try direct socket emit first
        io.to(socketId).emit('new-notification', notification);
        
        // Also emit to the user room as backup
        io.to(`user-${userId}`).emit('new-notification', notification);
        
        res.status(201).json({
          success: true,
          message: 'Task assignment notification sent in real-time',
          notification
        });
      } else {
        console.log(`User ${userId} is not connected, notification saved for later delivery`);
        res.status(201).json({
          success: true,
          message: 'Task assignment notification saved but user not connected',
          notification
        });
      }
    } else {
      res.status(500).json({ error: 'Failed to retrieve created notification' });
    }
  } catch (error) {
    console.error('Error sending task assignment notification:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Routes to check past due tasks on demand
app.post('/check-past-due-tasks', async (req, res) => {
  try {
    const { check_date } = req.body;
    
    if (!check_date) {
      return res.status(400).json({ error: 'Missing check_date parameter' });
    }
    
    // Call the API to check for tasks due soon
    const response = await fetch(`${process.env.API_BASE_URL || 'http://localhost:8080'}/tasks/check-due-soon`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const result = await response.json();
      console.log(`Checked for tasks due soon: ${result.data?.notifications_sent || 0} notification(s) sent.`);
      res.status(200).json({ 
        success: true, 
        message: 'Due soon tasks checked',
        notifications_sent: result.data?.notifications_sent || 0
      });
    } else {
      console.error('Failed to check for tasks due soon:', await response.text());
      res.status(500).json({ error: 'Failed to check for tasks due soon' });
    }
  } catch (error) {
    console.error('Error checking for tasks due soon:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});


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
checkDueSoonTasks();