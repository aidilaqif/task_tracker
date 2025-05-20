// Set up cache version and name
const CACHE_VERSION = 'v1';
const CACHE_NAME = 'notification-cache-' + CACHE_VERSION;

// Create a broadcast channel for communication with client pages
const broadcastChannel = new BroadcastChannel('notification-channel');

// WebSocket connection and configuration
let socket = null;
let isConnected = false;
let userId = null;
let serverUrl = 'http://localhost:3000'; // Default fallback
let reconnectTimer = null;
let reconnectAttempts = 0;
const MAX_RECONNECT_ATTEMPTS = 10;
const RECONNECT_DELAY = 3000; // 3 seconds initial delay

// Store notifications to sync with new clients
let notificationStore = [];

// Service Worker lifecycle events
self.addEventListener('install', event => {
    console.log('[Notification Worker] Installing Service Worker');
    self.skipWaiting(); // Activate immediately
});

self.addEventListener('activate', event => {
    console.log('[Notification Worker] Service Worker activated');
    event.waitUntil(self.clients.claim()); // Take control of clients immediately
});

// Handle messages from clients (web pages)
self.addEventListener('message', event => {
    console.log('[Notification Worker] Message received:', event.data);

    // Initialize connection with user details
    if (event.data.type === 'INIT') {
        userId = event.data.userId;
        if (event.data.serverUrl) {
            serverUrl = event.data.serverUrl;
        }

        // Connect if not already connected
        if (!isConnected && userId) {
            connectToNotificationServer();
        }

        // Send all stored notifications to the new client
        event.ports[0].postMessage({
            type: 'INIT_RESPONSE',
            notifications: notificationStore,
            connected: isConnected
        });
    }

    // Mark notification as read
    else if (event.data.type === 'MARK_READ') {
        markNotificationAsRead(event.data.notificationId);

        // Update notification in store
        const notificationIndex = notificationStore.findIndex(n => n.id == event.data.notificationId);
        if (notificationIndex >= 0) {
            notificationStore[notificationIndex].is_read = true;
            // Broadcast the update to all clients
            broadcastChannel.postMessage({
                type: 'NOTIFICATION_READ',
                notificationId: event.data.notificationId
            });
        }
    }

    // Mark all notifications as read
    else if (event.data.type === 'MARK_ALL_READ') {
        markAllNotificationsAsRead();

        // Update all notifications in store
        notificationStore.forEach(notification => {
            notification.is_read = true;
        });

        // Broadcast the update to all clients
        broadcastChannel.postMessage({
            type: 'ALL_NOTIFICATIONS_READ'
        });
    }

    // Manually fetch notifications
    else if (event.data.type === 'FETCH_NOTIFICATIONS') {
        fetchNotificationsFromServer();
    }

    // Disconnect (usually on logout)
    else if (event.data.type === 'DISCONNECT') {
        disconnectFromNotificationServer();
        userId = null;
        notificationStore = [];

        // Notify client
        if (event.ports && event.ports[0]) {
            event.ports[0].postMessage({
                type: 'DISCONNECTED'
            });
        }
    }
});

// Connect to the notification server
function connectToNotificationServer() {
    if (!userId || isConnected) return;

    try {
        // Import Socket.IO client from CDN (workers can't use importScripts for ESM)
        importScripts('https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.8.1/socket.io.min.js');

        console.log('[Notification Worker] Connecting to server:', serverUrl);

        // Close existing socket if any
        if (socket) {
            try {
                socket.disconnect();
            } catch (e) {
                console.error('[Notification Worker] Error disconnecting socket:', e);
            }
        }

        // Create new socket connection
        socket = io(serverUrl, {
            path: '/socket.io',
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionAttempts: MAX_RECONNECT_ATTEMPTS,
            reconnectionDelay: RECONNECT_DELAY,
            reconnectionDelayMax: 30000, // 30 seconds max delay
            timeout: 20000
        });

        // Socket event handlers
        socket.on('connect', () => {
            console.log('[Notification Worker] Connected to notification server');
            isConnected = true;
            reconnectAttempts = 0;

            // Clear any pending reconnect timers
            if (reconnectTimer) {
                clearTimeout(reconnectTimer);
                reconnectTimer = null;
            }

            // Authenticate with user ID
            socket.emit('authenticate', userId);

            // Notify all clients
            broadcastChannel.postMessage({
                type: 'CONNECTION_STATUS',
                connected: true
            });
        });

        socket.on('disconnect', (reason) => {
            console.log('[Notification Worker] Disconnected from server:', reason);
            isConnected = false;

            // Notify all clients
            broadcastChannel.postMessage({
                type: 'CONNECTION_STATUS',
                connected: false,
                reason: reason
            });

            // Handle reconnection for certain disconnect reasons
            if (reason === 'io server disconnect' || reason === 'transport close') {
                handleReconnection();
            }
        });

        socket.on('error', (error) => {
            console.error('[Notification Worker] Socket error:', error);
            isConnected = false;

            // Notify all clients
            broadcastChannel.postMessage({
                type: 'CONNECTION_ERROR',
                error: error.message || 'Unknown error'
            });

            handleReconnection();
        });

        socket.on('connect_error', (error) => {
            console.error('[Notification Worker] Connection error:', error);
            isConnected = false;

            // Notify all clients
            broadcastChannel.postMessage({
                type: 'CONNECTION_ERROR',
                error: error.message || 'Connection error'
            });

            handleReconnection();
        });

        // Handle new notifications
        socket.on('new-notification', (notification) => {
            console.log('[Notification Worker] New notification received:', notification);

            // Add to notification store
            addNotificationToStore(notification);

            // Broadcast to all clients
            broadcastChannel.postMessage({
                type: 'NEW_NOTIFICATION',
                notification: notification
            });

            // IMPORTANT ADDITION: Send directly to all clients as well
            // This ensures the notification is delivered even if broadcast fails
            sendToAllClients({
                type: 'NEW_NOTIFICATION',
                notification: notification
            });
        });

        // Handle initial unread notifications
        socket.on('unread-notifications', (notifications) => {
            console.log('[Notification Worker] Received unread notifications:', notifications);

            if (Array.isArray(notifications)) {
                // Update notification store with all unread notifications
                notifications.forEach(notification => {
                    addNotificationToStore(notification);
                });

                // Broadcast to all clients
                broadcastChannel.postMessage({
                    type: 'UNREAD_NOTIFICATIONS',
                    notifications: notifications
                });
            }
        });

        // Handle authentication success
        socket.on('authenticated', (data) => {
            console.log('[Notification Worker] Authentication successful:', data);

            // Broadcast to all clients
            broadcastChannel.postMessage({
                type: 'AUTHENTICATED',
                data: data
            });
        });

    } catch (e) {
        console.error('[Notification Worker] Error connecting to notification server:', e);
        isConnected = false;
        handleReconnection();
    }
}

// Handle reconnection
function handleReconnection() {
    if (reconnectTimer) {
        clearTimeout(reconnectTimer);
    }

    if (reconnectAttempts >= MAX_RECONNECT_ATTEMPTS) {
        console.log('[Notification Worker] Maximum reconnection attempts reached');
        return;
    }

    reconnectAttempts++;

    // Exponential backoff for reconnection
    const delay = Math.min(
        RECONNECT_DELAY * Math.pow(1.5, reconnectAttempts),
        30000 // Max 30 seconds
    );

    console.log(`[Notification Worker] Reconnecting in ${delay / 1000} seconds (attempt ${reconnectAttempts})`);

    reconnectTimer = setTimeout(() => {
        if (userId) {
            connectToNotificationServer();
        }
    }, delay);
}

// Disconnect from notification server
function disconnectFromNotificationServer() {
    if (socket) {
        try {
            console.log('[Notification Worker] Disconnecting from notification server');
            socket.disconnect();
            socket = null;
            isConnected = false;

            // Notify all clients
            broadcastChannel.postMessage({
                type: 'CONNECTION_STATUS',
                connected: false,
                reason: 'manual_disconnect'
            });

        } catch (e) {
            console.error('[Notification Worker] Error disconnecting:', e);
        }
    }

    // Clear any reconnection timers
    if (reconnectTimer) {
        clearTimeout(reconnectTimer);
        reconnectTimer = null;
    }
}

// Add notification to store (avoiding duplicates)
function addNotificationToStore(notification) {
    if (!notification || !notification.id) return;

    const existingIndex = notificationStore.findIndex(n => n.id === notification.id);

    if (existingIndex >= 0) {
        // Update existing notification
        notificationStore[existingIndex] = notification;
    } else {
        // Add new notification
        notificationStore.unshift(notification);
    }

    // Keep store size reasonable (max 100 notifications)
    if (notificationStore.length > 100) {
        notificationStore = notificationStore.slice(0, 100);
    }

    // Sort by creation date (newest first)
    notificationStore.sort((a, b) => {
        return new Date(b.created_at) - new Date(a.created_at);
    });
}

// Mark notification as read (API call)
function markNotificationAsRead(notificationId) {
    if (!notificationId) return;

    // Create a request to mark notification as read
    fetch(`/admin/notifications/mark-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (!data.status) {
                console.error('[Notification Worker] Failed to mark notification as read:', data.msg);
            }
        })
        .catch(error => {
            console.error('[Notification Worker] Error marking notification as read:', error);
        });
}

// Mark all notifications as read (API call)
function markAllNotificationsAsRead() {
    fetch('/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (!data.status) {
                console.error('[Notification Worker] Failed to mark all notifications as read:', data.msg);
            }
        })
        .catch(error => {
            console.error('[Notification Worker] Error marking all notifications as read:', error);
        });
}

// Fetch notifications from server (API call)
function fetchNotificationsFromServer() {
    fetch('/admin/notifications?limit=20&unread=1', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data && data.data.notifications) {
                const notifications = data.data.notifications;

                // Update notification store
                notifications.forEach(notification => {
                    addNotificationToStore(notification);
                });

                // Broadcast to all clients
                broadcastChannel.postMessage({
                    type: 'NOTIFICATIONS_FETCHED',
                    notifications: notifications
                });
            } else {
                console.error('[Notification Worker] Failed to fetch notifications:', data.msg);
            }
        })
        .catch(error => {
            console.error('[Notification Worker] Error fetching notifications:', error);
        });
}

// Sends messages directly to all active clients
async function sendToAllClients(message) {
    try {
        const clients = await self.clients.matchAll({ includeUncontrolled: true, type: 'window' });
        if (clients && clients.length > 0) {
            console.log(`[Notification Worker] Sending message to ${clients.length} clients`);
            clients.forEach(client => {
                client.postMessage(message);
            });
        }
    } catch (e) {
        console.error('[Notification Worker] Error sending to clients:', e);
    }
}

// Self ping to keep service worker alive
setInterval(() => {
    console.log('[Notification Worker] Ping');

    // If disconnected but should be connected, try to reconnect
    if (!isConnected && userId && !reconnectTimer) {
        console.log('[Notification Worker] Detected disconnection, attempting to reconnect');
        connectToNotificationServer();
    }
}, 60000); // Every minute