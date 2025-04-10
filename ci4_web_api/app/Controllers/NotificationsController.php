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

    // Send notification for a new task assignment
    public function sendTaskNotification()
    {
        $input = $this->request->getJSON();

        // Validate required fields
        if (!isset($input->user_id) || !isset($input->task_id) || !isset($input->title) || !isset($input->message)) {
            return $this->respondWithJson(false, "User ID, Task ID, Title and Message are required", null, 400);
        }

        // Verify that the user exists
        $user = $this->usersModel->find($input->user_id);
        if (!$user) {
            return $this->respondWithJson(false, "User not found", null, 404);
        }

        // Verify that the task exists
        $tasks = $this->tasksModel->find($input->task_id);
        if (!$tasks) {
            return $this->respondWithJson(false, "Tasks not found", null, 404);
        }

        // Create notification
        $data = [
            'user_id' => $input->user_id,
            'task_id' => $input->task_id,
            'title' => $input->title,
            'message' => $input->message,
            'is_read' => false
        ];

        try {
            if ($this->notificationsModel->insert($data)) {
                $notificationId = $this->notificationsModel->getInsertID();
                $notification = $this->notificationsModel->find($notificationId);

                return $this->respondWithJson(true, "Notification sent successfully", $notification);
            } else {
                $errors = $this->notificationsModel->errors();
                return $this->respondWithJson(false, "Failed to send notification", $errors, 400);
            }
        } catch (\Exception $e) {
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
        if(!$notification) {
            return $this->respondWithJson(false, "Notification not found", null, 404);
        }

        try {
            // Update the notification to mark it as read
            if ($this->notificationsModel->update($notificationId, ['is_read' => 1])) {
                // Get the updated notification
                $updatedNotification = $this->notificationsModel->find($notificationId);
                return $this->respondWithJson(true, "Notification marked as read", $updatedNotification);
            } else {
                $errors = $this->notificationsModel->errors();
                return $this->respondWithJson(false, "Failed to mark notification as read", $errors, 400);
            }
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
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
