<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\NotificationsModel;
use App\Models\UsersModel;
use App\Models\TasksModel;

class NotificationsController extends BaseController
{
    protected $notificationsModel;
    protected $usersModel;
    protected $tasksModel;

    public function __construct()
    {
        $this->notificationsModel = new NotificationsModel();
        $this->usersModel = new UsersModel();
        $this->tasksModel = new TasksModel();
    }

    public function sendTaskAssignmentNotification()
    {
        $input = $this->request->getJSON();
        log_message('info', 'Received task assignment notification request: ' . json_encode($input));

        // Validate required fields
        if (!isset($input->task_id) || !isset($input->user_id)) {
            log_message('error', 'Missing required fields in task assignment notification request');
            return $this->respondWithJson(false, "Task ID and User ID are required", null, 400);
        }

        // Verify that the user exists
        $user = $this->usersModel->find($input->user_id);
        if (!$user) {
            log_message('error', "User {$input->user_id} not found for task assignment notification");
            return $this->respondWithJson(false, "User not found", null, 404);
        }

        // Verify that the task exists
        $task = $this->tasksModel->find($input->task_id);
        if (!$task) {
            log_message('error', "Task {$input->task_id} not found for task assignment notification");
            return $this->respondWithJson(false, "Task not found", null, 404);
        }

        // Initialize TasksController to use its notification method
        $tasksController = new \App\Controllers\TasksController();
        
        // Call the centralized method
        $result = $tasksController->createTaskNotification($task, 'assignment');
        
        if ($result) {
            // Get the latest notification for this task and user
            $notification = $this->notificationsModel->where('user_id', $input->user_id)
                ->where('task_id', $input->task_id)
                ->orderBy('id', 'DESC')
                ->first();

            return $this->respondWithJson(true, "Task assignment notification sent", $notification);
        } else {
            log_message('error', "Failed to create task assignment notification from API");
            return $this->respondWithJson(false, "Failed to send notification", null, 500);
        }
    }
    // Send notification for a new task assignment
    public function sendTaskNotification()
    {
        $input = $this->request->getJSON();

        // Validate required fields
        if (!isset($input->user_id) || !isset($input->task_id) || !isset($input->title) || !isset($input->message)) {
            return $this->respondWithJson(false, "User ID, Task ID, Title and Message are required", null, 400);
        }

        $type = $input->type ?? 'general';

        // Verify that the user exists
        $user = $this->usersModel->find($input->user_id);
        if (!$user) {
            return $this->respondWithJson(false, "User not found", null, 404);
        }

        // Verify that the task exists
        $task = $this->tasksModel->find($input->task_id);
        if (!$task) {
            return $this->respondWithJson(false, "Task not found", null, 404);
        }

        // Check for recent duplicate notifications (within 15 seconds)
        $existingNotification = $this->notificationsModel->where('user_id', $input->user_id)
            ->where('task_id', $input->task_id)
            ->where('title', $input->title)
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-15 seconds')))
            ->first();

        if ($existingNotification) {
            log_message('info', "Duplicate notification prevented via API for task {$input->task_id}, user {$input->user_id}");
            return $this->respondWithJson(true, "Notification already exists", $existingNotification);
        }

        // Create notification with transaction
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $data = [
                'user_id' => $input->user_id,
                'task_id' => $input->task_id,
                'title' => $input->title,
                'message' => $input->message,
                'is_read' => false,
                'type' => $type,
            ];

            if ($this->notificationsModel->insert($data)) {
                $notificationId = $this->notificationsModel->getInsertID();
                $notification = $this->notificationsModel->find($notificationId);

                // Use TasksController's method to send to notification server
                $tasksController = new \App\Controllers\TasksController();
                $tasksController->sendToNotificationServer(['id' => $input->task_id, 'user_id' => $input->user_id], $notificationId);

                $db->transCommit();
                return $this->respondWithJson(true, "Notification sent successfully", $notification);
            } else {
                $errors = $this->notificationsModel->errors();
                $db->transRollback();
                return $this->respondWithJson(false, "Failed to send notification", $errors, 400);
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', "Error sending notification via API: " . $e->getMessage());
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }

    // Get notifications for a specific user
    public function getUserNotifications($userId)
    {
        // Verify that the user exists
        $user = $this->usersModel->find($userId);
        if (!$user) {
            return $this->respondWithJson(false, "User not found", null, 404);
        }

        // Get query parameters for filtering
        $isRead = $this->request->getGet('is_read');

        // Initialize query builder
        $builder = $this->notificationsModel->where('user_id', $userId);

        // Apply is_read filter if provided
        if ($isRead !== null) {
            $isReadBool = filter_var($isRead, FILTER_VALIDATE_BOOLEAN);
            $builder->where('is_read', $isReadBool ? 1 : 0);
        }

        // Order by most recent first
        $builder->orderBy('created_at', 'DESC');

        // Execute the query
        $notifications = $builder->findAll();

        return $this->respondWithJson(
            true,
            $notifications ? "Notifications retrieved successfully" : "No notifications found for this user",
            $notifications ?: []
        );
    }
    // Mark a notification as read
    public function markAsRead($notificationId)
    {
        // Verify that the notification exists
        $notification = $this->notificationsModel->find($notificationId);
        if (!$notification) {
            return $this->respondWithJson(false, "Notification not found", null, 404);
        }

        try {
            // Check if already marked as read
            if ($notification['is_read']) {
                return $this->respondWithJson(true, "Notification already marked as read", $notification);
            }

            // Update the notification to mark it as read
            if ($this->notificationsModel->update($notificationId, ['is_read' => 1])) {
                // Get the updated notification
                $updatedNotification = $this->notificationsModel->find($notificationId);

                // Log the update
                log_message('info', "Notification {$notificationId} marked as read");

                return $this->respondWithJson(true, "Notification marked as read", $updatedNotification);
            } else {
                $errors = $this->notificationsModel->errors();
                return $this->respondWithJson(false, "Failed to mark notification as read", $errors, 400);
            }
        } catch (\Exception $e) {
            log_message('error', "Error marking notification as read: " . $e->getMessage());
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    private function sendToNotificationServer($task, $notificationId = null)
    {
        try {
            // Get user data
            $usersModel = new \App\Models\UsersModel();
            $user = $usersModel->find($task['user_id']);
            
            if (!$user) {
                log_message('error', "Cannot find user {$task['user_id']} for notification server");
                return false;
            }
    
            // Log additional diagnostic information
            log_message('info', "Sending notification to server for task {$task['id']} to user {$user['id']}, notificationId: " . ($notificationId ?? 'null'));
    
            // Get the notification server URL from environment
            $notificationServerUrl = getenv('NOTIFICATION_SERVER_URL');
            if (empty($notificationServerUrl)) {
                $notificationServerUrl = 'http://localhost:3000'; // Default fallback
                log_message('warning', "Using default notification server URL: {$notificationServerUrl}");
            } else {
                log_message('info', "Using configured notification server URL: {$notificationServerUrl}");
            }
    
            // Find the notification to send
            $notificationsModel = new \App\Models\NotificationsModel();
            $notification = null;
            
            if ($notificationId) {
                // Use the provided notification ID
                $notification = $notificationsModel->find($notificationId);
                if (!$notification) {
                    log_message('error', "Notification ID {$notificationId} not found in database");
                    return false;
                }
            } else {
                // Find the most recent notification for this task and user
                $notification = $notificationsModel->where('user_id', $user['id'])
                    ->where('task_id', $task['id'])
                    ->orderBy('id', 'DESC')
                    ->first();
                    
                if (!$notification) {
                    log_message('warning', "No notification found for user {$user['id']} and task {$task['id']} - cannot send to notification server");
                    return false;
                }

                $notificationId = $notification['id'];
            }

            log_message('info', "Sending notification ID {$notification['id']} to real-time server for user {$user['id']} and task {$task['id']}");

            // Create the notification server request with notification ID
            $client = \Config\Services::curlrequest();

            // Log the request payload for debugging
            $payload = [
                'notification_id' => (int)$notification['id'],
                'user_id' => (int)$user['id'],
                'task_id' => (int)$task['id'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'type' => $notification['type'] ?? 'general',
                'is_read' => false
            ];
            
            log_message('debug', "Notification server payload: " . json_encode($payload));
            
            // Try sending to the real-time server's direct notification endpoint
            $response = $client->request(
                'POST',
                $notificationServerUrl . '/send-notification',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'json' => $payload,
                    'timeout' => 5
                ]
            );
    
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody();
            
            log_message('info', "Notification server response: HTTP {$statusCode}, Body: {$responseBody}");
    
            if ($statusCode >= 200 && $statusCode < 300) {
                log_message('info', "Successfully sent notification {$notificationId} to notification server");
                return true;
            } else {
                log_message('error', "Failed to send notification {$notificationId} to server: HTTP {$statusCode}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Exception sending to notification server: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }
    // Standard JSON response method
    private function respondWithJson($status, $msg, $data = null, $statusCode = 200)
    {
        $response = [
            "status" => $status,
            "msg" => $msg
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->response->setJSON($response)->setStatusCode($statusCode);
    }
}
