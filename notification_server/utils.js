// Maximum number of notifications to track
const MAX_TRACKED_NOTIFICATIONS = 1000;

// Set to track processed notifications
const processedNotifications = new Set();

// Track notification to avoid duplicates
function trackNotification(notificationId) {
    // Add to processed set
    processedNotifications.add(notificationId);

    // Keep the set size manageable
    if (processedNotifications.size > MAX_TRACKED_NOTIFICATIONS) {
        // Convert to array, keep only the most recent entries
        const notificationArray = Array.from(processedNotifications);
        const startIndex = notificationArray.length - Math.floor(MAX_TRACKED_NOTIFICATIONS / 2);
        processedNotifications.clear();

        for (let i = startIndex; i < notificationArray.length; i++) {
            processedNotifications.add(notificationArray[i]);
        }
    }
}

// Check if notification has been processed
function isNotificationProcessed(notificationId) {
    return processedNotifications.has(notificationId);
}

// Helper function to infer notification type from title
function inferNotificationType(title) {
    title = (title || '').toLowerCase();
    if (title.includes('assigned')) {
        return 'assignment';
    } else if (title.includes('status')) {
        return 'status';
    } else if (title.includes('priority')) {
        return 'priority';
    } else if (title.includes('progress')) {
        return 'progress';
    } else if (title.includes('due date')) {
        return 'due_date';
    } else {
        return 'general';
    }
}

// Simple logging
function log(level, message, data) {
    const timestamp = new Date().toISOString();
    const logEntry = {
        timestamp,
        level,
        message,
        ...(data && { data })
    };

    if (level === 'error') {
        console.error(`[${timestamp}] ERROR: ${message}`, data || '');
    } else {
        console.log(`[${timestamp}] ${level.toUpperCase()}: ${message}`, data || '');
    }
}

module.exports = {
    trackNotification,
    isNotificationProcessed,
    inferNotificationType,
    log,
    processedNotifications
};