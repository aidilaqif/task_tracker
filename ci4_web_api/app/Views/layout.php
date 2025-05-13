<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Task Tracker' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


    <!-- Base CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>">

    <!-- Ensure sidebar CSS is always loaded -->
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar.css') ?>">

    <!-- Component-specific CSS -->
    <?php if(isset($css_files) && is_array($css_files)): ?>
        <?php foreach($css_files as $css): ?>
            <link rel="stylesheet" href="<?= base_url('assets/css/components/' . $css . '.css') ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Check for authentication on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to AJAX requests to handle session expiration
            const originalFetch = window.fetch;
            window.fetch = function(url, options) {
                return originalFetch(url, options).then(response => {
                    if (response.status === 401) {
                        // Session expired, redirect to login
                        alert('Your session has expired. Please login again.');
                        window.location.href = '/login';
                        return Promise.reject(new Error('Session expired'));
                    }
                    return response;
                });
            };
        });
    </script>
</head>
<body>
    <!-- Notification Overlay -->
    <div id="notificationOverlay" class="notification-overlay"></div>
    <div id="toastContainer" class="toast-container"></div>
    <div id="notificationPanel" class="notification-panel">
        <div class="notification-header">
            <h3>Notifications</h3>
            <button id="markAllReadBtn" class="mark-all-read">Mark All Read</button>
        </div>
        <div id="notificationList" class="notification-list">
            <!-- Notifications will be loaded here -->
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications yet</p>
            </div>
        </div>
    </div>

    <!-- Include Sidebar -->
    <?= $this->include('sidebar') ?>

    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Page Header -->
        <header class="content-header">
            <div class="container">
                <h1><?= $header ?? $title ?? 'Task Tracker' ?></h1>
                <?php if(session()->has('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                <?php if(session()->has('success')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <!-- Main Content -->
        <main class="content-container">
            <div class="container">
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/socket.io-client@4.6.1/dist/socket.io.min.js"></script>
    <script>
        // Configuration
        const NOTIFICATION_SERVER_URL = '<?= getenv('NOTIFICATION_SERVER_URL')?>';
        const CURRENT_USER_ID = <?= session()->get('user_id') ?: 'null' ?>;
    </script>
    <script src="<?= base_url('assets/js/notifications.js') ?>"></script>
</body>
</html>