document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationCount = document.getElementById('notificationCount');
    const notificationPanel = document.getElementById('notificationPanel');
    const notificationList = document.getElementById('notificationList');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const notificationOverlay = document.getElementById('notificationOverlay');
    const toastContainer = document.getElementById('toastContainer');

    // Skip if not logged in
    if (!CURRENT_USER_ID) {
        console.log('User not logged in, skipping notification initialization');
        return;
    }

    // Initialize socket connection
    const socket = io(NOTIFICATION_SERVER_URL, {
        transports: ['websocket', 'polling']
    });

    let unreadNotifications = [];
    let isNotificationPanelOpen = false;

    // Socket connection events
    socket.on('connect', function() {
        console.log('Connected to notification server');
        socket.emit('authenticate', CURRENT_USER_ID);
    });

    socket.on('authenticated', function(data) {
        console.log('Authenticated with notification server:', data);
        loadNotifications();
    });

    socket.on('connect_error', function(error) {
        console.error('Connection error:', error);
    });

    socket.on('disconnect', function() {
        console.log('Disconnected from notification server');
    });

    // Handle new notifications
    socket.on('new-notification', function(notification) {
        console.log('Received new notification:', notification);

        // Add to unread count
        if (!notification.is_read) {
            unreadNotifications.push(notification);
            updateNotificationBadge();
        }

        // Show toast notification
        showToast(notification);

        // Add to panel if open
        if (isNotificationPanelOpen) {
            addNotificationToPanel(notification);
        }
    });

    // Handle batch of unread notifications
    socket.on('unread-notifications', function(notifications) {
        console.log('Received unread notifications:', notifications);

        // Add all to unread list
        for (const notification of notifications) {
            if (!notification.is_read) {
                unreadNotifications.push(notification);
            }
        }

        updateNotificationBadge();

        // Add to panel if open
        if (isNotificationPanelOpen) {
            refreshNotificationPanel();
        }
    });

    // Toggle notification panel
    notificationBadge.addEventListener('click', function() {
        toggleNotificationPanel();
    });

    // Mark all as read
    markAllReadBtn.addEventListener('click', function() {
        markAllNotificationsAsRead();
    });

    // Close panel when clicking overlay
    notificationOverlay.addEventListener('click', function() {
        closeNotificationPanel();
    });

    // Load notifications from API
    function loadNotifications() {
        fetch(`/notifications/user/${CURRENT_USER_ID}?is_read=0`)
            .then(response => response.json())
            .then(data => {
                if (data.status && data.data) {
                    unreadNotifications = data.data;
                    updateNotificationBadge();

                    if (isNotificationPanelOpen) {
                        refreshNotificationPanel();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
            });
    }

    // Update notification badge
    function updateNotificationBadge() {
        const count = unreadNotifications.length;
        notificationCount.textContent = count > 99 ? '99+' : count;

        if (count > 0) {
            notificationCount.classList.add('has-notifications');
        } else {
            notificationCount.classList.remove('has-notifications');
        }
    }

    // Toggle notification panel
    function toggleNotificationPanel() {
        if (isNotificationPanelOpen) {
            closeNotificationPanel();
        } else {
            openNotificationPanel();
        }
    }

    // Open notification panel
    function openNotificationPanel() {
        notificationPanel.classList.add('show');
        notificationOverlay.classList.add('show');
        isNotificationPanelOpen = true;
        refreshNotificationPanel();
    }

    // Close notification panel
    function closeNotificationPanel() {
        notificationPanel.classList.remove('show');
        notificationOverlay.classList.remove('show');
        isNotificationPanelOpen = false;
    }

    // Refresh notification panel content
    function refreshNotificationPanel() {
        // First, get all notifications (read and unread)
        fetch(`/notifications/user/${CURRENT_USER_ID}`)
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    renderNotifications(data.data || []);
                } else {
                    showEmptyState('Failed to load notifications');
                }
            })
            .catch(error => {
                console.error('Error refreshing notifications:', error);
                showEmptyState('Error loading notifications');
            });
    }

    // Render notifications in panel
    function renderNotifications(notifications) {
        // Clear existing content
        notificationList.innerHTML = '';

        if (notifications.length === 0) {
            showEmptyState();
            return;
        }

        // Sort notifications by date (newest first)
        notifications.sort((a, b) => {
            return new Date(b.created_at) - new Date(a.created_at);
        });

        // Create elements for each notification
        for (const notification of notifications) {
            addNotificationToPanel(notification, false);
        }
    }

    // Add a single notification to the panel
    function addNotificationToPanel(notification, prepend = true) {
        // Create notification item
        const item = document.createElement('div');
        item.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
        item.dataset.id = notification.id;

        // Determine icon based on notification type
        let iconClass = 'bell';
        let typeClass = '';

        if (notification.title.includes('Assigned')) {
            iconClass = 'tasks';
            typeClass = 'task-assigned';
        } else if (notification.title.includes('Status')) {
            iconClass = 'sync-alt';
            typeClass = 'task-status';
        } else if (notification.title.includes('Due Soon')) {
            iconClass = 'clock';
            typeClass = 'task-due-soon';
        } else if (notification.title.includes('Priority')) {
            iconClass = 'exclamation-triangle';
            typeClass = 'task-priority';
        } else if (notification.title.includes('Extension')) {
            iconClass = 'hourglass-half';
            typeClass = 'task-extension';
        }

        // Format date
        const date = new Date(notification.created_at);
        const formattedDate = date.toLocaleString();

        // Create HTML content
        item.innerHTML = `
            <div class="notification-icon">
                <i class="fas fa-${iconClass}"></i>
            </div>
            <div class="notification-title">${notification.title}</div>
            <div class="notification-message">${notification.message}</div>
            <div class="notification-meta">
                <div class="notification-time">${formattedDate}</div>
            </div>
            ${!notification.is_read ? `
            <div class="notification-actions">
                <button class="mark-read-btn" title="Mark as read">
                    <i class="fas fa-check"></i>
                </button>
            </div>
            ` : ''}
        `;

        // Add class for notification type
        if (typeClass) {
            item.classList.add(typeClass);
        }

        // Add click handler to view task
        if (notification.task_id) {
            item.addEventListener('click', function(e) {
                if (!e.target.closest('.mark-read-btn')) {
                    window.location.href = `/task_detail?task_id=${notification.task_id}`;
                }
            });
        }

        // Add click handler for mark as read button
        const markReadBtn = item.querySelector('.mark-read-btn');
        if (markReadBtn) {
            markReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                markNotificationAsRead(notification.id);
            });
        }

        // Add to notification list
        if (prepend) {
            notificationList.prepend(item);
            // Add animation class for new notifications
            setTimeout(() => {
                item.classList.add('notification-new');
                setTimeout(() => {
                    item.classList.remove('notification-new');
                }, 1000);
            }, 100);
        } else {
            notificationList.appendChild(item);
        }
    }

    // Show empty state
    function showEmptyState(message = 'No notifications yet') {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <p>${message}</p>
            </div>
        `;
    }

    // Mark a notification as read
    function markNotificationAsRead(notificationId) {
        fetch(`/notifications/read/${notificationId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Update UI
                const item = notificationList.querySelector(`.notification-item[data-id="${notificationId}"]`);
                if (item) {
                    item.classList.remove('unread');
                    const actions = item.querySelector('.notification-actions');
                    if (actions) {
                        actions.remove();
                    }
                }

                // Update local state
                unreadNotifications = unreadNotifications.filter(n => n.id != notificationId);
                updateNotificationBadge();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    // Mark all notifications as read
    function markAllNotificationsAsRead() {
        if (unreadNotifications.length === 0) {
            return;
        }

        const promises = unreadNotifications.map(notification => {
            return fetch(`/notifications/read/${notification.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
        });

        Promise.all(promises)
            .then(() => {
                // Update UI
                const items = notificationList.querySelectorAll('.notification-item.unread');
                items.forEach(item => {
                    item.classList.remove('unread');
                    const actions = item.querySelector('.notification-actions');
                    if (actions) {
                        actions.remove();
                    }
                });

                // Update local state
                unreadNotifications = [];
                updateNotificationBadge();
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
    }

    // Show toast notification
    function showToast(notification) {
        const toast = document.createElement('div');
        toast.className = 'toast';

        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-title">${notification.title}</div>
                <button class="toast-close">&times;</button>
            </div>
            <div class="toast-body">${notification.message}</div>
        `;

        // Add close button handler
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', function() {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        });

        // Add click handler to view task
        if (notification.task_id) {
            toast.addEventListener('click', function(e) {
                if (!e.target.closest('.toast-close')) {
                    window.location.href = `/task_detail?task_id=${notification.task_id}`;
                }
            });
            toast.style.cursor = 'pointer';
        }

        // Add to container
        toastContainer.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Auto close after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    }
});