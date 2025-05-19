// Manages notification data and Socket.IO connection
// Store notifications
let notifications = [];
let socket;

// Initialize Socket.IO connection
function initializeSocket() {
    // Get server URL - ideally this should be set in a config
    const serverUrl = window.notificationServerUrl || 'http://localhost:3000';

    console.log('Connecting to notification server at:', serverUrl);

    // Initialize Socket.IO connection
    socket = io(serverUrl, {
        path: '/socket.io',
        transports: ['websocket', 'polling'],
        reconnection: true,
        reconnectionAttempts: 10,
        reconnectionDelay: 1000,
        reconnectionDelayMax: 5000,
        timeout: 20000
    });

    // Handle socket connection events
    socket.on('connect', () => {
        console.log('Connected to notification server');
        authenticateUser();
    });

    socket.on('disconnect', (reason) => {
        console.log('Disconnected from notification server:', reason);

        if (reason === 'io server disconnect') {
            socket.connect();
        }
        // Try to reconnect after a delay
        setTimeout(() => {
            console.log('Attempting to reconnect...');
            socket.connect();
        }, 5000);
    });

    socket.on('connect_error', (error) => {
        console.error('Connection error:', error);
    });

    // Handle notification events
    socket.on('new-notification', (notification) => {
        console.log('New notification received:', notification);
        playNotificationSound();
        addNotification(notification);
    });

    socket.on('unread-notifications', (notifications) => {
        console.log('Unread notifications received:', notifications);
        if (Array.isArray(notifications)) {
            notifications.forEach(notification => {
                addNotification(notification);
            });
        }
    });

    // Return socket for external use
    return socket;
}

// Authenticate user with Socket.IO server
function authenticateUser() {
    // Get user ID from a global variable set in layout.php
    const userId = window.userId;

    if (!userId || userId <= 0) {
        console.error('User ID not available for authentication');
        return;
    }

    console.log('Authenticating user with ID:', userId);

    // Emit authentication event with user ID
    socket.emit('authenticate', userId);

    // Handle authentication response
    socket.once('authenticated', (data) => {
        console.log('Authentication successful:', data);
    });

    socket.once('error', (error) => {
        console.error('Authentication error:', error);
    });
}

// Optional: Play a notification sound
function playNotificationSound() {
    const audio = new Audio('/assets/sounds/notification.mp3');
    audio.play().catch(e => console.log('Sound play prevented by browser policy'));
}

// Add notification to list
function addNotification(notification) {
    // Validate notification object
    if (!notification || !notification.id) {
        console.error('Invalid notification object:', notification);
        return;
    }

    // Check if notification already exists
    const existingIndex = notifications.findIndex(n => n.id === notification.id);

    if (existingIndex >= 0) {
        // Update existing notification
        notifications[existingIndex] = notification;
    } else {
        // Add new notification
        notifications.unshift(notification); // Add to beginning
    }

    // Sort by created date (newest first)
    notifications.sort((a, b) => {
        return new Date(b.created_at) - new Date(a.created_at);
    });

    // Update UI (badge and dropdown)
    updateNotificationUI();
}

// Get unread notification count
function getUnreadCount() {
    return notifications.filter(n => !n.is_read).length;
}

// Update the badge to show the current unread count
function updateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (!badge) return;

    const count = getUnreadCount();

    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
}
// Mark notification as read
function markNotificationAsRead(notificationId) {
    // Update local state immediately for better UX
    const notification = notifications.find(n => n.id == notificationId);
    if (notification) {
        notification.is_read = true;
        updateNotificationUI();
    }

    // Call API to update server
    fetch(`/admin/notifications/mark-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (!data.status) {
                console.error('Failed to mark notification as read:', data.msg);
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
}

// Mark all notifications as read
function markAllAsRead() {
    // Update local state immediately
    notifications.forEach(notification => {
        notification.is_read = true;
    });
    updateNotificationUI();

    // Call API to update server
    fetch('/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (!data.status) {
                console.error('Failed to mark all notifications as read:', data.msg);
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
}

// Fetch unread count from API
function fetchUnreadCount() {
    fetch('/admin/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                document.getElementById('notificationBadge').textContent = data.data;
                document.getElementById('notificationBadge').style.display =
                    data.data > 0 ? 'flex' : 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching unread count:', error);
        });
}

// Fetch initial notifications for dropdown
function fetchInitialNotifications() {
    fetch('/admin/notifications?limit=5&unread=1')
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data && data.data.notifications) {
                // Add to notification list
                data.data.notifications.forEach(notification => {
                    addNotification(notification);
                });

                // Update UI
                updateNotificationUI();
            }
        })
        .catch(error => {
            console.error('Error fetching initial notifications:', error);
        });
}
// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function () {
    if (window.userId && window.userId > 0) {
        // Initialize Socket.IO
        const socket = initializeSocket();

        // Fetch initial data for faster UI updates
        fetchUnreadCount();
        fetchInitialNotifications();
    } else {
        console.log('User not logged in, skipping Socket.IO initialization');
    }
});

document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible' && socket && !socket.connected) {
        console.log('Page became visible, reconnecting socket...');
        socket.connect();
    }
});