require('dotenv').config();
const mysql = require('mysql2/promise');

// Database configuration from .env
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

// Test database connection
async function testDatabaseConnection() {
  console.log('Testing database connection with config:', {
    host: dbConfig.host,
    port: dbConfig.port,
    user: dbConfig.user,
    database: dbConfig.database
  });
  
  try {
    const pool = mysql.createPool(dbConfig);
    const connection = await pool.getConnection();
    console.log('Database connection successful!');
    
    // Test query to verify notifications table exists and has the correct structure
    const [tableInfo] = await connection.query('DESCRIBE notifications');
    console.log('Notifications table structure:');
    console.table(tableInfo);
    
    // Check for any unread notifications
    const [unreadCount] = await connection.query('SELECT COUNT(*) as count FROM notifications WHERE is_read = 0');
    console.log('Unread notifications count:', unreadCount[0].count);
    
    connection.release();
    await pool.end();
  } catch (error) {
    console.error('Database connection error:', error.message);
    process.exit(1);
  }
}

// Run the test
testDatabaseConnection();