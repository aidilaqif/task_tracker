// Create a namespace for our notification system
const NotificationSystem = (function () {
    // Private variables
    let notifications = [];
    let socket;

    // Private functions
    function initializeSocket() {
        const serverUrl = window.notificationServerUrl || 'http://localhost:3000';

        if (socket && socket.connected) {
            console.log('Socket already connected, skipping initialization');
            return socket;
        }

        console.log('Connecting to notification server at:', serverUrl);

        // Check if server is reachable before attempting connection
        fetch(`${serverUrl}/health`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' },
            signal: AbortSignal.timeout(2000)
        })
            .then(() => {
                // Only connect if server is reachable
                initSocketConnection(serverUrl);
            })
            .catch(error => {
                console.warn('Notification server may be unavailable:', error.message);
                window.retrySocketConnection = true;

                // Fall back to REST API for notifications
                fetchNotificationsManually();
            });
    }

    function initSocketConnection(serverUrl) {
        socket = io(serverUrl, {
            path: '/socket.io',
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionAttempts: 5,
            reconnectionDelay: 2000,
            reconnectionDelayMax: 10000,
            timeout: 20000,
            autoConnect: false
        });

        // Socket event handlers
        socket.on('connect', () => {
            console.log('Connected to notification server');
            authenticateUser();
            window.retrySocketConnection = false;
        });

        socket.on('disconnect', (reason) => {
            console.log('Disconnected from notification server:', reason);
            if (reason === 'io server disconnect' || reason === 'transport close') {
                setTimeout(() => {
                    if (document.visibilityState === 'visible') {
                        console.log('Attempting to reconnect...');
                        socket.connect();
                    }
                }, 5000);
            }
        });

        socket.on('connect_error', (error) => {
            console.error('Connection error:', error);
            if (socket.io && typeof socket.io.reconnectionDelay === 'function') {
                socket.io.reconnectionDelay(5000);
            }
        });

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

        socket.connect();
        return socket;
    }

    function authenticateUser() {
        const userId = window.userId;

        if (!userId || userId <= 0) {
            console.error('User ID not available for authentication');
            return;
        }

        console.log('Authenticating user with ID:', userId);
        socket.emit('authenticate', userId);
    }

    function playNotificationSound() {
        try {
            const audio = new Audio('/assets/sounds/notification.mp3');
            audio.play().catch(e => console.log('Sound play prevented by browser policy'));
        } catch (e) {
            console.log('Error playing notification sound:', e);
        }
    }

    function addNotification(notification) {
        try {
            if (!notification || !notification.id) {
                console.error('Invalid notification object:', notification);
                return;
            }

            const existingIndex = notifications.findIndex(n => n.id === notification.id);

            if (existingIndex >= 0) {
                notifications[existingIndex] = notification;
            } else {
                notifications.unshift(notification);
            }

            // Sort by created date (newest first)
            notifications.sort((a, b) => {
                return new Date(b.created_at) - new Date(a.created_at);
            });

            // Update UI components
            updateNotificationBadge();
            updateNotificationDropdown();
        } catch (e) {
            console.error('Error adding notification:', e);
        }
    }

    function getUnreadCount() {
        return notifications.filter(n => !n.is_read).length;
    }

    function updateNotificationBadge() {
        const badge = document.getElementById('notificationBadge');
        if (!badge) return;

        const count = getUnreadCount();
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }

    function updateNotificationDropdown() {
        const list = document.getElementById('notificationList');
        if (!list) return;

        // Clear existing content
        list.innerHTML = '';

        // Get notifications for dropdown (limit to 5)
        const dropdownNotifications = notifications.slice(0, 5);

        if (dropdownNotifications.length === 0) {
            // Show empty state
            list.innerHTML = `
                <div class="empty-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>No new notifications</p>
                </div>
            `;
            return;
        }

        // Create a Map to track notifications by task and type
        const notificationMap = new Map();

        // Process notifications
        dropdownNotifications.forEach(notification => {
            // Create a key for similar notifications
            const key = `${notification.task_id}-${notification.type}`;

            // Only keep the most recent notification for each task/type combination
            if (!notificationMap.has(key) ||
                new Date(notification.created_at) > new Date(notificationMap.get(key).created_at)) {
                notificationMap.set(key, notification);
            }
        });

        // Render unique notifications
        Array.from(notificationMap.values())
            .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            .forEach(notification => {
                const item = renderNotificationItem(notification);
                list.appendChild(item);
            });
    }

    function renderNotificationItem(notification) {
        // Create notification item element
        const item = document.createElement('div');
        item.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
        item.dataset.id = notification.id;

        if (notification.task_id) {
            item.dataset.taskId = notification.task_id;
        }

        // Determine icon based on notification type
        let iconHtml = getNotificationTypeIcon(notification.type);

        // Format time ago
        const timeAgo = formatTimeAgo(new Date(notification.created_at));

        item.innerHTML = `
            <div class="notification-icon">${iconHtml}</div>
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${timeAgo}</div>
            </div>
        `;

        // Add click handler
        item.addEventListener('click', function () {
            try {
                const notificationId = this.dataset.id;
                if (!notificationId) {
                    console.error('Missing notification ID');
                    return;
                }

                // Mark as read
                markNotificationAsRead(notificationId);

                // Navigate to related task if it has task_id
                const taskId = this.dataset.taskId;
                if (taskId) {
                    window.location.href = `/task_detail?task_id=${taskId}`;
                }
            } catch (error) {
                console.error('Error handling notification click:', error);
            }
        });

        return item;
    }

    function getNotificationTypeIcon(type) {
        switch (type) {
            case 'assignment':
                return '<i class="fas fa-clipboard-check text-primary"></i>';
            case 'status':
                return '<i class="fas fa-tasks text-info"></i>';
            case 'priority':
                return '<i class="fas fa-flag text-warning"></i>';
            case 'progress':
                return '<i class="fas fa-chart-line text-success"></i>';
            case 'due_date':
                return '<i class="fas fa-calendar-alt text-danger"></i>';
            default:
                return '<i class="fas fa-bell text-secondary"></i>';
        }
    }

    function formatTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);

        let interval = Math.floor(seconds / 31536000);
        if (interval >= 1) {
            return interval === 1 ? '1 year ago' : `${interval} years ago`;
        }

        interval = Math.floor(seconds / 2592000);
        if (interval >= 1) {
            return interval === 1 ? '1 month ago' : `${interval} months ago`;
        }

        interval = Math.floor(seconds / 86400);
        if (interval >= 1) {
            return interval === 1 ? '1 day ago' : `${interval} days ago`;
        }

        interval = Math.floor(seconds / 3600);
        if (interval >= 1) {
            return interval === 1 ? '1 hour ago' : `${interval} hours ago`;
        }

        interval = Math.floor(seconds / 60);
        if (interval >= 1) {
            return interval === 1 ? '1 minute ago' : `${interval} minutes ago`;
        }

        return 'Just now';
    }

    function markNotificationAsRead(notificationId) {
        try {
            // Validate notificationId
            if (!notificationId) {
                console.error('Invalid notification ID');
                return;
            }

            // Update local state immediately for better UX
            const notification = notifications.find(n => n.id == notificationId);
            if (notification) {
                notification.is_read = true;
                updateNotificationBadge();
                updateNotificationDropdown();
            }

            // Call API to update server
            fetch(`/admin/notifications/mark-read/${notificationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.status) {
                        console.error('Failed to mark notification as read:', data.msg);
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
        } catch (error) {
            console.error('Exception in markNotificationAsRead:', error);
        }
    }

    function markAllAsRead() {
        notifications.forEach(notification => {
            notification.is_read = true;
        });

        updateNotificationBadge();
        updateNotificationDropdown();

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

    function fetchUnreadCount() {
        fetch('/admin/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        badge.textContent = data.data;
                        badge.style.display = data.data > 0 ? 'flex' : 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching unread count:', error);
            });
    }

    function fetchInitialNotifications() {
        fetch('/admin/notifications?limit=5&unread=1')
            .then(response => response.json())
            .then(data => {
                if (data.status && data.data && data.data.notifications) {
                    data.data.notifications.forEach(notification => {
                        addNotification(notification);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching initial notifications:', error);
            });
    }

    function fetchNotificationsManually() {
        if (socket && socket.connected) return;

        console.log('Using API fallback to fetch notifications');
        fetchUnreadCount();
        fetchInitialNotifications();
    }

    function setupNotificationInteractions() {
        const bell = document.getElementById('notificationBell');
        const dropdown = document.getElementById('notificationDropdown');
        const markAllLink = document.getElementById('markAllAsRead');

        if (!bell || !dropdown) return;

        bell.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');

            if (dropdown.classList.contains('show')) {
                fetchInitialNotifications();
            }
        });

        document.addEventListener('click', function (e) {
            if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });

        if (markAllLink) {
            markAllLink.addEventListener('click', function (e) {
                e.preventDefault();
                markAllAsRead();
            });
        }
    }

    function init() {
        if (window.userId && window.userId > 0) {
            initializeSocket();
            fetchUnreadCount();
            fetchInitialNotifications();
            setupNotificationInteractions();

            // Set up page visibility handler
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    if (window.retrySocketConnection || (socket && !socket.connected)) {
                        console.log('Page visible again, attempting to reconnect socket...');
                        window.retrySocketConnection = false;

                        setTimeout(() => {
                            if (socket) socket.disconnect();
                            initializeSocket();
                        }, 1000);
                    }
                }
            });

            // Set up periodic checks
            setInterval(fetchNotificationsManually, 60000);
        } else {
            console.log('User not logged in, skipping notification system initialization');
        }
    }

    // Public API
    return {
        init: init,
        markNotificationAsRead: markNotificationAsRead,
        markAllAsRead: markAllAsRead,
        getUnreadCount: getUnreadCount
    };
})();

// Initialize the notification system when the document is ready
document.addEventListener('DOMContentLoaded', function () {
    NotificationSystem.init();
});