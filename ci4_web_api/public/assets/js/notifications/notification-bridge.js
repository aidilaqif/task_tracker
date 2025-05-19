// Create a notification system namespace
const NotificationBridge = (function () {
    // Private variables
    let notifications = [];
    let isConnected = false;
    let channel = null;
    let worker = null;
    let isInitialized = false;
    let pendingTasks = [];
    let socketBackupMode = false; // Backup direct socket connection if needed
    let directSocket = null;

    // Create a broadcast channel for communication with the service worker
    function setupChannel() {
        if (channel) return;

        try {
            channel = new BroadcastChannel('notification-channel');

            // Handle messages from the service worker
            channel.onmessage = function (event) {
                console.log('Received broadcast message:', event.data);
                handleWorkerMessage(event.data);
            };

            // Test channel by sending a ping message
            setTimeout(() => {
                if (channel) {
                    try {
                        channel.postMessage({ type: 'PING' });
                        console.log('Broadcast channel ping sent');
                    } catch (e) {
                        console.error('Error sending channel ping:', e);
                    }
                }
            }, 1000);

            console.log('Broadcast channel created');
        } catch (e) {
            console.error('Error creating broadcast channel:', e);
            // Initialize backup mode
            initBackupMode();
        }
    }

    // Initialize direct socket as backup if Service Worker approach fails
    function initBackupMode() {
        console.log('Initializing backup socket mode');
        socketBackupMode = true;

        if (!window.userId) {
            console.log('No user ID available for backup mode');
            return;
        }

        // Create a direct socket connection
        try {
            const serverUrl = window.notificationServerUrl || 'http://localhost:3000';

            console.log('Connecting direct socket to:', serverUrl);
            directSocket = io(serverUrl, {
                path: '/socket.io',
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionAttempts: 5,
                reconnectionDelay: 2000
            });

            directSocket.on('connect', () => {
                console.log('Direct socket connected');
                isConnected = true;

                // Authenticate
                directSocket.emit('authenticate', window.userId);
            });

            directSocket.on('new-notification', (notification) => {
                console.log('Direct socket received notification:', notification);
                playNotificationSound();
                addNotification(notification);
                updateNotificationBadge();
                updateNotificationDropdown();
            });

            directSocket.on('unread-notifications', (notifications) => {
                console.log('Direct socket received unread notifications:', notifications);
                if (Array.isArray(notifications)) {
                    notifications.forEach(notification => {
                        addNotification(notification);
                    });
                    updateNotificationBadge();
                    updateNotificationDropdown();
                }
            });

            // More event handlers as needed
        } catch (e) {
            console.error('Error initializing backup socket:', e);
        }
    }

    // Register the service worker
    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            console.error('Service Worker not supported in this browser');
            initBackupMode();
            return Promise.reject(new Error('Service Worker not supported'));
        }

        return navigator.serviceWorker.register('/assets/js/notifications/notification-worker.js')
            .then(registration => {
                console.log('Service Worker registered with scope:', registration.scope);

                // Get the active service worker
                if (registration.active) {
                    worker = registration.active;
                    console.log('Active worker found');
                }

                // Listen for controller change (when the SW takes control)
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    worker = navigator.serviceWorker.controller;
                    console.log('Service Worker controller changed');

                    // Reinitialize with the worker
                    if (window.userId) {
                        initializeWorker();
                    }
                });

                // Set the controller if available
                if (navigator.serviceWorker.controller) {
                    worker = navigator.serviceWorker.controller;
                    console.log('Controller found:', !!worker);
                }

                // Set up message listener for all worker messages
                navigator.serviceWorker.addEventListener('message', (event) => {
                    console.log('Direct message from Service Worker:', event.data);
                    handleWorkerMessage(event.data);
                });

                return registration;
            })
            .catch(error => {
                console.error('Service Worker registration failed:', error);
                initBackupMode();
                throw error;
            });
    }

    // Initialize the service worker with user info
    function initializeWorker() {
        if (!worker || !window.userId) {
            console.log('Deferring initialization (worker or userId not ready)');

            // Set a timeout to try again if worker doesn't become available
            setTimeout(() => {
                if (!isInitialized && window.userId) {
                    console.log('Retry initialization - worker may still be starting up');
                    if (navigator.serviceWorker.controller) {
                        worker = navigator.serviceWorker.controller;
                        initializeWorker();
                    } else if (!socketBackupMode) {
                        // Fall back to direct socket if worker still not available
                        initBackupMode();
                    }
                }
            }, 2000);

            return Promise.resolve(false);
        }

        console.log('Initializing worker with userId:', window.userId);

        return new Promise((resolve, reject) => {
            // Create a message channel for the response
            const messageChannel = new MessageChannel();

            // Set up the response handler
            messageChannel.port1.onmessage = event => {
                if (event.data.type === 'INIT_RESPONSE') {
                    console.log('Received init response:', event.data);

                    // Update connection status
                    isConnected = event.data.connected;

                    // Process stored notifications
                    if (event.data.notifications && Array.isArray(event.data.notifications)) {
                        event.data.notifications.forEach(notification => {
                            addNotification(notification);
                        });
                    }

                    // Update UI
                    updateNotificationBadge();
                    updateNotificationDropdown();

                    isInitialized = true;

                    // Process any pending tasks
                    processPendingTasks();

                    resolve(true);
                } else {
                    reject(new Error('Unexpected response type'));
                }
            };

            // Set a timeout for the response
            const initTimeout = setTimeout(() => {
                console.log('Worker initialization timed out - falling back to direct API');

                // Manually check for notifications even when worker times out
                fetchNotificationsFromAPI();

                // Fall back to direct socket if worker doesn't respond
                if (!isInitialized && !socketBackupMode) {
                    console.log('Activating backup mode after timeout');
                    initBackupMode();
                    isInitialized = true; // Consider it initialized to avoid hanging
                    processPendingTasks(); // Process any pending tasks
                }

                // Don't reject the promise - resolve with false instead
                resolve(false);
            }, 10000);

            try {
                // Send initialization message
                worker.postMessage({
                    type: 'INIT',
                    userId: window.userId,
                    serverUrl: window.notificationServerUrl || 'http://localhost:3000'
                }, [messageChannel.port2]);

                console.log('Initialization message sent to worker');
            } catch (e) {
                console.error('Error sending initialization message:', e);
                clearTimeout(initTimeout);

                // Fall back to direct socket if worker communication fails
                if (!socketBackupMode) {
                    initBackupMode();
                }

                reject(e);
            }
        }).catch(error => {
            console.error('Worker initialization failed:', error);
            return false;
        });
    }

    // Process any pending tasks
    function processPendingTasks() {
        console.log(`Processing ${pendingTasks.length} pending tasks`);
        while (pendingTasks.length > 0) {
            const task = pendingTasks.shift();
            task();
        }
    }

    // Handle messages from the service worker
    function handleWorkerMessage(message) {
        console.log('Processing worker message:', message);

        switch (message.type) {
            case 'NEW_NOTIFICATION':
                console.log('New notification received - updating UI');
                playNotificationSound();
                addNotification(message.notification);
                updateNotificationBadge();
                updateNotificationDropdown();
                break;

            case 'UNREAD_NOTIFICATIONS':
                console.log('Unread notifications received - updating UI');
                if (Array.isArray(message.notifications)) {
                    message.notifications.forEach(notification => {
                        addNotification(notification);
                    });
                    updateNotificationBadge();
                    updateNotificationDropdown();
                }
                break;

            case 'NOTIFICATION_READ':
                markLocalNotificationAsRead(message.notificationId);
                break;

            case 'ALL_NOTIFICATIONS_READ':
                markAllLocalNotificationsAsRead();
                break;

            case 'CONNECTION_STATUS':
                isConnected = message.connected;
                console.log('Connection status updated:', isConnected);
                break;

            case 'NOTIFICATIONS_FETCHED':
                console.log('Notifications fetched - updating UI');
                if (Array.isArray(message.notifications)) {
                    message.notifications.forEach(notification => {
                        addNotification(notification);
                    });
                    updateNotificationBadge();
                    updateNotificationDropdown();
                }
                break;
        }
    }

    // Fetch notifications directly from API
    function fetchNotificationsFromAPI() {
        fetch('/admin/notifications?limit=5&unread=1')
            .then(response => response.json())
            .then(data => {
                if (data.status && data.data && data.data.notifications) {
                    console.log('Fetched notifications from API:', data.data.notifications);
                    data.data.notifications.forEach(notification => {
                        addNotification(notification);
                    });
                    updateNotificationBadge();
                    updateNotificationDropdown();
                }
            })
            .catch(error => {
                console.error('Error fetching notifications from API:', error);
            });
    }

    // Add notification to local store
    function addNotification(notification) {
        if (!notification || !notification.id) return;

        console.log('Adding notification to local store:', notification.id);

        const existingIndex = notifications.findIndex(n => n.id === notification.id);

        if (existingIndex >= 0) {
            notifications[existingIndex] = notification;
        } else {
            notifications.unshift(notification);
        }

        // Keep max 50 notifications in memory
        if (notifications.length > 50) {
            notifications = notifications.slice(0, 50);
        }

        // Sort notifications by date
        notifications.sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
        });
    }

    // Mark a notification as read locally
    function markLocalNotificationAsRead(notificationId) {
        const notification = notifications.find(n => n.id == notificationId);
        if (notification) {
            notification.is_read = true;

            // Update UI
            updateNotificationBadge();
            updateNotificationDropdown();
        }
    }

    // Mark all notifications as read locally
    function markAllLocalNotificationsAsRead() {
        notifications.forEach(notification => {
            notification.is_read = true;
        });

        // Update UI
        updateNotificationBadge();
        updateNotificationDropdown();
    }

    // Mark a notification as read (via service worker or direct API)
    function markNotificationAsRead(notificationId) {
        if (!notificationId) return;

        // Mark locally first for instant feedback
        markLocalNotificationAsRead(notificationId);

        if (socketBackupMode || !worker) {
            // Use direct API call in backup mode
            fetch(`/admin/notifications/mark-read/${notificationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
            return;
        }

        // Ask service worker to mark it on the server
        const task = () => {
            worker.postMessage({
                type: 'MARK_READ',
                notificationId: notificationId
            });
        };

        if (isInitialized) {
            task();
        } else {
            pendingTasks.push(task);
        }
    }

    // Mark all notifications as read
    function markAllAsRead() {
        // Mark locally first for instant feedback
        markAllLocalNotificationsAsRead();

        if (socketBackupMode || !worker) {
            // Use direct API call in backup mode
            fetch('/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                });
            return;
        }

        // Ask service worker to mark all on the server
        const task = () => {
            worker.postMessage({
                type: 'MARK_ALL_READ'
            });
        };

        if (isInitialized) {
            task();
        } else {
            pendingTasks.push(task);
        }
    }

    // Fetch notifications
    function fetchNotifications() {
        if (socketBackupMode || !worker) {
            // Use direct API call in backup mode
            fetchNotificationsFromAPI();
            return;
        }

        const task = () => {
            worker.postMessage({
                type: 'FETCH_NOTIFICATIONS'
            });
        };

        if (isInitialized) {
            task();
        } else {
            pendingTasks.push(task);
        }
    }

    // Get unread notification count
    function getUnreadCount() {
        return notifications.filter(n => !n.is_read).length;
    }

    // Update notification badge
    function updateNotificationBadge() {
        const badge = document.getElementById('notificationBadge');
        if (!badge) return;

        const count = getUnreadCount();
        console.log(`Updating notification badge: ${count} unread notifications`);
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }

    // Play notification sound
    function playNotificationSound() {
        try {
            const audio = new Audio('/assets/sounds/notification.mp3');
            audio.play().catch(e => console.log('Sound play prevented by browser policy'));
        } catch (e) {
            console.log('Error playing notification sound:', e);
        }
    }

    // Update notification dropdown
    function updateNotificationDropdown() {
        const list = document.getElementById('notificationList');
        if (!list) return;

        console.log('Updating notification dropdown UI');

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

        // Render each notification
        dropdownNotifications.forEach(notification => {
            const item = renderNotificationItem(notification);
            list.appendChild(item);
        });
    }

    // Render a notification item in the dropdown
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

    // Get icon for notification type
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

    // Format time ago
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

    // Set up notification interactions
    function setupNotificationInteractions() {
        const bell = document.getElementById('notificationBell');
        const dropdown = document.getElementById('notificationDropdown');
        const markAllLink = document.getElementById('markAllAsRead');

        if (!bell || !dropdown) {
            console.warn('Notification UI elements not found in the DOM');
            return;
        }

        console.log('Setting up notification UI interactions');

        bell.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');

            if (dropdown.classList.contains('show')) {
                // Fetch latest notifications when opening dropdown
                fetchNotifications();
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

    // Clean up when the user logs out
    function cleanup() {
        if (worker) {
            try {
                worker.postMessage({
                    type: 'DISCONNECT'
                });
            } catch (e) {
                console.error('Error disconnecting worker:', e);
            }
        }

        if (directSocket) {
            try {
                directSocket.disconnect();
                directSocket = null;
            } catch (e) {
                console.error('Error disconnecting direct socket:', e);
            }
        }

        if (channel) {
            try {
                channel.close();
                channel = null;
            } catch (e) {
                console.error('Error closing channel:', e);
            }
        }

        notifications = [];
        isConnected = false;
        isInitialized = false;
        socketBackupMode = false;
    }

    // Initialize everything
    function init() {
        if (!window.userId || window.userId <= 0) {
            console.log('User not logged in, skipping notification system initialization');
            return;
        }

        console.log('Initializing notification bridge');

        // Setup broadcast channel
        setupChannel();

        // Register service worker
        registerServiceWorker()
            .then(() => {
                // Initialize with the worker once it's registered
                return initializeWorker();
            })
            .then(success => {
                console.log('Notification bridge initialized:', success ? 'successfully' : 'with issues');

                // Fetch initial notifications directly if needed
                if (!success && !socketBackupMode) {
                    console.log('Fetching initial notifications directly');
                    fetchNotificationsFromAPI();
                }
            })
            .catch(error => {
                console.error('Failed to initialize notification bridge:', error);

                // Fetch initial notifications as fallback
                fetchNotificationsFromAPI();
            });

        // Set up UI interactions
        setupNotificationInteractions();

        // Handle logout button if present
        const logoutLink = document.querySelector('a[href="/logout"]');
        if (logoutLink) {
            logoutLink.addEventListener('click', function () {
                cleanup();
            });
        }

        // Set an interval to periodically check for notifications
        // This works as a fallback if real-time updates aren't working
        setInterval(() => {
            console.log('Periodic notification check');
            fetchNotifications();
        }, 30000); // Every 30 seconds
    }

    // Public API
    return {
        init: init,
        markNotificationAsRead: markNotificationAsRead,
        markAllAsRead: markAllAsRead,
        getUnreadCount: getUnreadCount,
        fetchNotifications: fetchNotifications,
        cleanup: cleanup
    };
})();

// Initialize the notification bridge when the document is ready
document.addEventListener('DOMContentLoaded', function () {
    NotificationBridge.init();
});