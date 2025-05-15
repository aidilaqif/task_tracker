require('dotenv').config();

module.exports = {
    port: process.env.PORT || 3000,
    environment: process.env.NODE_ENV || 'development',
    pollInterval: parseInt(process.env.NOTIFICATION_POLL_INTERVAL, 10) || 5000,

    database: {
        host: process.env.DB_HOST,
        port: parseInt(process.env.DB_PORT, 10),
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME,
        waitForConnections: true,
        connectionLimit: 20,
        queueLimit: 0
    },

    socketIO: {
        cors: {
            origin: process.env.SOCKET_CORS_ORIGIN || "*",
            methods: ["GET", "POST"],
            credentials: true,
            allowedHeaders: ["*"]
        },
        path: process.env.SOCKET_PATH || "/socket.io"
    }
};