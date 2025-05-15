const db = require('../db');
const utils = require('../utils');

// Send notification route handler
async function sendNotification(req, res) {
    try {
        const { notification_id, user_id, task_id, title, message, type } = req.body;

        if (!notification_id || !user_id) {
            return res.status(400).json({ error: 'Missing required fields' });
        }

        // Convert IDs to integers
        const notificationId = parseInt(notification_id, 10);
        const userId = parseInt(user_id, 10);

        // Create notification object
        const notification = {
            id: notificationId,
            user_id: userId,
            task_id: task_id ? parseInt(task_id, 10) : null,
            title: title || '',
            message: message || '',
            is_read: false,
            type: type || utils.inferNotificationType(title || ''),
            created_at: new Date().toISOString()
        };

        // Track to avoid duplicate processing
        utils.trackNotification(notificationId);

        // Check if user is connected using the socketHandler
        const isConnected = req.socketHandler.isUserConnected(userId);

        if (isConnected) {
            // Send notification via socket
            req.socketHandler.sendNotification(userId, notification);

            res.status(200).json({
                success: true,
                message: 'Notification sent in real-time',
                notification_id: notificationId,
                connected: true
            });
        } else {
            res.status(200).json({
                success: true,
                message: 'Notification saved but user not connected',
                notification_id: notificationId,
                connected: false
            });
        }
    } catch (error) {
        utils.log('error', 'Error processing notification request:', error);
        res.status(500).json({ error: 'Internal server error: ' + error.message });
    }
}

// Task assignment notification handler
async function taskAssigned(req, res) {
    try {
        const { user_id, task_id, task_title } = req.body;

        if (!user_id || !task_id || !task_title) {
            return res.status(400).json({ error: 'Missing required fields' });
        }

        // Insert notification into database
        try {
            const [result] = await db.pool.query(`
        INSERT INTO notifications (user_id, task_id, title, message, is_read, type)
        VALUES (?, ?, ?, ?, 0, 'assignment')
      `, [user_id, task_id, 'Task Assigned', `You have been assigned to task: ${task_title}`]);

            if (result.insertId) {
                // Get the inserted notification
                const [notifications] = await db.pool.query(`
          SELECT * FROM notifications WHERE id = ?
        `, [result.insertId]);

                if (notifications.length > 0) {
                    const notification = notifications[0];
                    const userId = parseInt(user_id, 10);

                    // Track notification ID to avoid duplicates
                    utils.trackNotification(notification.id);

                    // Check if user is connected
                    const isConnected = req.socketHandler.isUserConnected(userId);

                    if (isConnected) {
                        // Send notification via socket
                        req.socketHandler.sendNotification(userId, notification);

                        res.status(201).json({
                            success: true,
                            message: 'Task assignment notification sent in real-time',
                            notification
                        });
                    } else {
                        res.status(201).json({
                            success: true,
                            message: 'Task assignment notification saved but user not connected',
                            notification
                        });
                    }
                } else {
                    res.status(500).json({ error: 'Failed to retrieve created notification' });
                }
            } else {
                res.status(500).json({ error: 'Failed to create notification' });
            }
        } catch (dbError) {
            utils.log('error', 'Database error in taskAssigned:', dbError);
            res.status(500).json({ error: 'Database error: ' + dbError.message });
        }
    } catch (error) {
        utils.log('error', 'Error sending task assignment notification:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
}

// Health check endpoint
function healthCheck(req, res) {
    res.status(200).json({
        status: 'ok',
        timestamp: new Date().toISOString(),
        environment: process.env.NODE_ENV,
        connections: req.socketHandler.getConnectedUsers().length
    });
}

// Get connected users (admin only)
function getConnectedUsers(req, res) {
    // Simple authorization check
    if (process.env.NODE_ENV === 'production') {
        const apiKey = req.headers['x-api-key'];
        if (apiKey !== process.env.ADMIN_API_KEY) {
            return res.status(401).json({ error: 'Unauthorized' });
        }
    }

    const users = req.socketHandler.getConnectedUsers();
    res.status(200).json({
        connectedUsers: users,
        count: users.length
    });
}

module.exports = {
    sendNotification,
    taskAssigned,
    healthCheck,
    getConnectedUsers
};