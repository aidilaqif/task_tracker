// Handles UI updates and user interactions

// Update notification UI
function updateNotificationUI() {
    // Update badge
    updateNotificationBadge();

    // Update dropdown
    updateNotificationDropdown();
}

// Update notification badge
function updateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    if (!badge) return;

    const count = getUnreadCount();

    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
}

// Update notification dropdown
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

    // Add notifications to dropdown

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
// Render notification item
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
            // Verify notification ID is valid before proceeding
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

// Mark notification as read
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
            updateNotificationUI();
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

// Helper function to get appropriate icon for notification type
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

// Setup notification UI interactions
function setupNotificationInteractions() {
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationDropdown');
    const markAllLink = document.getElementById('markAllAsRead');

    if (!bell || !dropdown) return;

    // Toggle dropdown on bell click
    bell.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('show');

        // When opening dropdown, check for new notifications
        if (dropdown.classList.contains('show')) {
            fetchInitialNotifications();
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Mark all as read
    if (markAllLink) {
        markAllLink.addEventListener('click', function (e) {
            e.preventDefault();
            markAllAsRead();
        });
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function () {
    setupNotificationInteractions();
});