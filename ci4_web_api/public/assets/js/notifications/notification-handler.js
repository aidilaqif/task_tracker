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
        transports: ['websocket', 'polling']
    });

    // Handle socket connection events
    socket.on('connect', () => {
        console.log('Connected to notification server');
        authenticateUser();
    });

    socket.on('disconnect', () => {
        console.log('Disconnected from notification server');
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
        notifications.unshift(notification);
    }

    // Sort by created date (newest first)
    notifications.sort((a, b) => {
        return new Date(b.created_at) - new Date(a.created_at);
    });

    // Update UI
    updateNotificationUI();

    // If we're on the notifications page, refresh the list
    if (window.location.pathname === '/notifications') {
        if (typeof loadNotifications === 'function') {
            loadNotifications();
        }
    }
}

// Get unread notification count
function getUnreadCount() {
    return notifications.filter(n => !n.is_read).length;
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

// Fetch initial unread count
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
                // Updated to use data.data.notifications instead of data.data
                data.data.notifications.forEach(notification => {
                    addNotification(notification);
                });

                // Update UI
                updateNotificationUI();
            } else {
                console.log('No notifications found or empty response');
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