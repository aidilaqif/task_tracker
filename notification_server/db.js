const mysql = require('mysql2/promise');
const config = require('./config');

// Create MySQL connection pool
const pool = mysql.createPool(config.database);

// Test database connection
async function testConnection() {
    try {
        const connection = await pool.getConnection();
        console.log('Database connection successful');
        connection.release();
        return true;
    } catch (error) {
        console.error('Database connection failed:', error.message);
        return false;
    }
}

// Get unread notifications for a user
async function getUnreadNotifications(userId) {
    try {
        const [rows] = await pool.query(`
        SELECT * FROM notifications 
        WHERE user_id = ? AND is_read = 0
        ORDER BY created_at DESC
        `, [userId]);

        return rows;
    } catch (error) {
        console.error(`Error getting unread notifications for user ${userId}:`, error);
        return [];
    }
}

// Check for new notifications
async function checkNewNotifications(intervalSeconds) {
    try {
        const [rows] = await pool.query(`
        SELECT * FROM notifications 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        AND is_read = 0
        `, [intervalSeconds]);

        return rows;
    } catch (error) {
        console.error('Error checking for new notifications:', error);
        return [];
    }
}

module.exports = {
    pool,
    testConnection,
    getUnreadNotifications,
    checkNewNotifications
};