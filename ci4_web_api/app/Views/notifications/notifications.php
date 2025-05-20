<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="notifications-container">
    <div class="page-header">
        <h2>Notifications</h2>
        <button id="markAllAsReadBtn" class="action-button refresh">
            <i class="fas fa-check-double"></i> Mark All as Read
        </button>
    </div>

    <div class="notifications-content">
        <div class="filters-container">
            <div class="search-container">
                <input type="text" id="searchNotifications" placeHolder="Search notifications...">
            </div>
            <div class="filter-options">
                <select id="filterType">
                    <option value="">All Types</option>
                    <option value="assignment">Assignment</option>
                    <option value="status">Status</option>
                    <option value="priority">Priority Change</option>
                    <option value="progress">Progress Update</option>
                    <option value="due_date">Due Date Change</option>
                    <option value="general">General</option>
                </select>
                <select id="filterRead">
                    <option value="">All</option>
                    <option value="0">Unread</option>
                    <option value="1">Read</option>
                </select>
            </div>
        </div>

        <div class="notification-list-container">
            <div id="notificationsList" class="notifications-list">
                <!-- Notifications will be loaded here via JS -->
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading notifications...</p>
                </div>
            </div>

            <div id="emptyNotifications" class="empyt-state" style="display: none;">
                <i class="fas fa-bell-slash"></i>
                <h3>No Notifications</h3>
                <p>You don't have any notifications yet.</p>
            </div>

            <div class="pagination-container" id="paginationContainer">
                <!-- Pagination will be added here via JS -->
            </div>
        </div>
    </div>
</div>

<script>
    <?= $this->include('notifications/js/notifications.js') ?>
</script>
<?= $this->endSection() ?>