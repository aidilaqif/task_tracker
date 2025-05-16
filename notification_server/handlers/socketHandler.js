const db = require('../db');
const utils = require('../utils');
const config = require('../config');

// Connected clients registry - maps user IDs to their socket IDs
const connectedUsers = new Map();

// Initialize Socket.IO
function initSocketIO(io) {
    // Socket.IO connection handling
    io.on('connection', (socket) => {
        utils.log('info', `New client connected: ${socket.id}`);

        // Client authenticates with their user ID
        socket.on('authenticate', async (userId) => {
            try {
                // Ensure userId is an integer
                userId = parseInt(userId, 10);

                if (isNaN(userId) || userId <= 0) {
                    utils.log('error', `Invalid user ID for authentication: ${userId}`);
                    socket.emit('error', { message: 'Invalid user ID' });
                    return;
                }

                utils.log('info', `User ${userId} authenticated on socket ${socket.id}`);

                // Check if this is an admin user (for debugging)
                const db = require('../db');
                const [rows] = await db.pool.query('SELECT role FROM users WHERE id = ?', [userId]);
                const isAdmin = rows.length > 0 && rows[0].role === 'admin';
                if (isAdmin) {
                    utils.log('info', `Admin user ${userId} connected - will receive all system notifications`);
                }

                // Store socket ID mapped to user ID (replacing any existing connection)
                connectedUsers.set(userId, socket.id);

                // Join user-specific room
                socket.join(`user-${userId}`);

                // Let client know they've been authenticated
                socket.emit('authenticated', {
                    userId,
                    timestamp: new Date().toISOString(),
                    isAdmin: isAdmin
                });

                // Fetch and send any unread notifications to this user
                const unreadNotifications = await db.getUnreadNotifications(userId);

                if (unreadNotifications.length > 0) {
                    utils.log('info', `Sending ${unreadNotifications.length} unread notifications to user ${userId}`);

                    // Process notifications to ensure they have types
                    const processedNotifications = unreadNotifications.map(notification => {
                        if (!notification.type) {
                            notification.type = utils.inferNotificationType(notification.title || '');
                        }
                        return notification;
                    });

                    // Send all unread notifications in a batch
                    io.to(`user-${userId}`).emit('unread-notifications', processedNotifications);
                }
            } catch (error) {
                utils.log('error', `Authentication error for socket ${socket.id}:`, error);
                socket.emit('error', { message: 'Authentication failed: ' + error.message });
            }
        });

        // Notification received acknowledgement
        socket.on('notification-received', (data) => {
            if (data && data.notificationId) {
                utils.log('info', `Client acknowledged receipt of notification ${data.notificationId}`);
            }
        });

        // Handle disconnection
        socket.on('disconnect', () => {
            // Remove from connected users
            for (const [userId, socketId] of connectedUsers.entries()) {
                if (socketId === socket.id) {
                    utils.log('info', `User ${userId} disconnected (socket ${socket.id})`);
                    connectedUsers.delete(userId);
                    break;
                }
            }
        });
    });

    // Return methods for external use
    return {
        async checkForNewNotifications() {
            try {
                const pollIntervalSeconds = config.pollInterval / 1000 * 3;
                const newNotifications = await db.checkNewNotifications(pollIntervalSeconds);

                if (newNotifications.length === 0) {
                    return;
                }

                utils.log('info', `Found ${newNotifications.length} new notifications in this poll interval`);

                // Filter out notifications we've already processed
                const unprocessedNotifications = newNotifications.filter(notification =>
                    !utils.isNotificationProcessed(notification.id)
                );

                if (unprocessedNotifications.length === 0) {
                    return;
                }

                utils.log('info', `Processing ${unprocessedNotifications.length} new notifications`);

                // Track these notifications as processed to avoid duplicates
                unprocessedNotifications.forEach(notification => {
                    utils.trackNotification(notification.id);
                });

                // Group notifications by user_id for more efficient processing
                const notificationsByUser = {};
                for (const notification of unprocessedNotifications) {
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

                        // Send each notification individually for immediate delivery
                        for (const notification of notifications) {
                            // Ensure notification has a type
                            if (!notification.type) {
                                notification.type = utils.inferNotificationType(notification.title || '');
                            }

                            // Send directly to the user's socket
                            io.to(socketId).emit('new-notification', notification);

                            // Also emit to the user's room as backup
                            io.to(`user-${userIdInt}`).emit('new-notification', notification);
                        }
                    }
                }
            } catch (error) {
                utils.log('error', 'Error checking for new notifications:', error);
            }
        },

        isUserConnected(userId) {
            return connectedUsers.has(userId);
        },

        sendNotification(userId, notification) {
            if (connectedUsers.has(userId)) {
                const socketId = connectedUsers.get(userId);
                io.to(socketId).emit('new-notification', notification);
                io.to(`user-${userId}`).emit('new-notification', notification);
                return true;
            }
            return false;
        },

        getConnectedUsers() {
            return Array.from(connectedUsers.keys());
        }
    };
}

module.exports = {
    initSocketIO
};