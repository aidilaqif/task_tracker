let currentPage = 1;
let currentLimit = 10;
let currentFilters = {
    type: '',
    is_read: '',
    search: ''
};
let totalPages = 1;

document.addEventListener('DOMContentLoaded', function () {
    // Load initial notifications
    loadNotifications();

    // Add event listeners for filters
    document.getElementById('filterType').addEventListener('change', function () {
        currentFilters.type = this.value;
        currentPage = 1; // Reset to first page when filtering
        loadNotifications();
    });

    document.getElementById('filterRead').addEventListener('change', function () {
        currentFilters.is_read = this.value;
        currentPage = 1; // Reset to first page when filtering
        loadNotifications();
    });

    document.getElementById('searchNotifications').addEventListener('input', function () {
        currentFilters.search = this.value;
        // Add debounce to prevent too many requests
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            currentPage = 1; // Reset to first page when searching
            loadNotifications();
        }, 300);
    });

    // Add event listener for Mark All as Read button
    document.getElementById('markAllAsReadBtn').addEventListener('click', function () {
        markAllAsRead();
    });
});

function loadNotifications() {
    showLoading();

    // Build query parameters
    let queryParams = `page=${currentPage}&limit=${currentLimit}`;

    if (currentFilters.type) {
        queryParams += `&type=${currentFilters.type}`;
    }

    if (currentFilters.is_read !== '') {
        queryParams += `&unread=${currentFilters.is_read === '0' ? '1' : '0'}`;
    }

    if (currentFilters.search) {
        queryParams += `&search=${encodeURIComponent(currentFilters.search)}`;
    }

    // Fetch notifications
    fetch(`/admin/notifications?${queryParams}`)
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                renderNotifications(data.data.notifications);
                renderPagination(data.data.pagination);
            } else {
                showError(data.msg || 'Failed to load notifications');
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showError('Error loading notifications. Please try again.');
        })
        .finally(() => {
            hideLoading();
        });
}

function renderNotifications(notifications) {
    const notificationsList = document.getElementById('notificationsList');
    const emptyState = document.getElementById('emptyNotifications');

    // Clear previous content
    notificationsList.innerHTML = '';

    // Check if we have notifications
    if (!notifications || notifications.length === 0) {
        emptyState.style.display = 'block';
        return;
    }

    // Hide empty state
    emptyState.style.display = 'none';

    // Render each notification
    notifications.forEach(notification => {
        const card = document.createElement('div');
        card.className = `notification-card ${notification.is_read ? '' : 'unread'}`;
        card.dataset.id = notification.id;

        // Format date
        const date = new Date(notification.created_at);
        const formattedDate = formatDate(date);

        // Determine type label
        const typeLabel = getTypeLabel(notification.type);

        card.innerHTML = `
            <div class="notification-header">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-time">${formattedDate}</div>
            </div>
            <div class="notification-message">${notification.message}</div>
            <div class="notification-footer">
                <div class="notification-type ${notification.type || 'general'}">${typeLabel}</div>
                <div class="notification-actions">
                    ${notification.task_id ?
                `<button class="view-task" data-task-id="${notification.task_id}">View Task</button>` : ''}
                    ${!notification.is_read ?
                `<button class="mark-read" data-id="${notification.id}">Mark as Read</button>` : ''}
                </div>
            </div>
        `;

        notificationsList.appendChild(card);
    });

    // Add event listeners for action buttons
    addActionEventListeners();
}

function renderPagination(pagination) {
    const paginationContainer = document.getElementById('paginationContainer');
    totalPages = pagination.total_pages;

    // Clear previous pagination
    paginationContainer.innerHTML = '';

    // No need for pagination if only one page
    if (totalPages <= 1) {
        return;
    }

    const ul = document.createElement('ul');
    ul.className = 'pagination';

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = currentPage === 1 ? 'disabled' : '';
    const prevLink = document.createElement('a');
    prevLink.href = '#';
    prevLink.innerHTML = '&laquo;';
    if (currentPage > 1) {
        prevLink.addEventListener('click', function (e) {
            e.preventDefault();
            currentPage--;
            loadNotifications();
        });
    }
    prevLi.appendChild(prevLink);
    ul.appendChild(prevLi);

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, startPage + 4);

    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = i === currentPage ? 'active' : '';
        const link = document.createElement('a');
        link.href = '#';
        link.textContent = i;
        link.addEventListener('click', function (e) {
            e.preventDefault();
            currentPage = i;
            loadNotifications();
        });
        li.appendChild(link);
        ul.appendChild(li);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = currentPage === totalPages ? 'disabled' : '';
    const nextLink = document.createElement('a');
    nextLink.href = '#';
    nextLink.innerHTML = '&raquo;';
    if (currentPage < totalPages) {
        nextLink.addEventListener('click', function (e) {
            e.preventDefault();
            currentPage++;
            loadNotifications();
        });
    }
    nextLi.appendChild(nextLink);
    ul.appendChild(nextLi);

    paginationContainer.appendChild(ul);
}

function addActionEventListeners() {
    // Add event listeners for View Task buttons
    document.querySelectorAll('.view-task').forEach(button => {
        button.addEventListener('click', function () {
            const taskId = this.dataset.taskId;
            window.location.href = `/task_detail?task_id=${taskId}`;
        });
    });

    // Add event listeners for Mark as Read buttons
    document.querySelectorAll('.mark-read').forEach(button => {
        button.addEventListener('click', function () {
            const notificationId = this.dataset.id;
            markNotificationAsRead(notificationId);
        });
    });
}

function markNotificationAsRead(notificationId) {
    fetch(`/admin/notifications/mark-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Update UI
                const card = document.querySelector(`.notification-card[data-id="${notificationId}"]`);
                if (card) {
                    card.classList.remove('unread');
                    const markReadBtn = card.querySelector('.mark-read');
                    if (markReadBtn) {
                        markReadBtn.remove();
                    }
                }
            } else {
                showError(data.msg || 'Failed to mark notification as read');
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
            showError('Error marking notification as read. Please try again.');
        });
}

function markAllAsRead() {
    // Confirm with user
    if (!confirm('Are you sure you want to mark all notifications as read?')) {
        return;
    }

    fetch('/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Reload notifications
                loadNotifications();
            } else {
                showError(data.msg || 'Failed to mark all notifications as read');
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
            showError('Error marking all notifications as read. Please try again.');
        });
}

function showLoading() {
    const notificationsList = document.getElementById('notificationsList');
    const emptyState = document.getElementById('emptyNotifications');

    emptyState.style.display = 'none';
    notificationsList.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading notifications...</p>
        </div>
    `;
}

function hideLoading() {
    // The loading spinner will be replaced by the content
}

function showError(message) {
    const notificationsList = document.getElementById('notificationsList');
    const emptyState = document.getElementById('emptyNotifications');

    emptyState.style.display = 'none';
    notificationsList.innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            ${message}
        </div>
    `;
}

function formatDate(date) {
    // Format the date as "Apr 23, 2023 at 14:30"
    const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options).replace(',', ' at');
}

function getTypeLabel(type) {
    switch (type) {
        case 'assignment':
            return 'Assignment';
        case 'status':
            return 'Status Update';
        case 'priority':
            return 'Priority Change';
        case 'progress':
            return 'Progress Update';
        case 'due_date':
            return 'Due Date';
        default:
            return 'General';
    }
}