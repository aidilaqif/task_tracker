<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TasksModel;
use App\Models\UsersModel;
use App\Models\NotificationsModel;
// use CodeIgniter\HTTP\ResponseInterface;

class TasksController extends BaseController
{
    protected $tasksModel;

    public function __construct()
    {
        $this->tasksModel = new TasksModel();
        $this->notificationsModel = new NotificationsModel();
    }

    // Get a list of all tasks
    public function getAllTasks()
    {
        $tasks = $this->tasksModel->findAll();

        if ($tasks) {
            // Initialize UsersModel
            $usersModel = new \App\Models\UsersModel();

            // Get all users and create a map of user IDs to names
            $users = $usersModel->findAll();
            $userMap = [];

            foreach ($users as $user) {
                $userMap[$user['id']] = $user['name'];
            }

            // Replace user_id with name in each task
            foreach ($tasks as &$task) {
                $userId = $task['user_id'];
                // Check if the user_id exists in map
                if (isset($userMap[$userId])) {
                    $task['assigned_to'] = $userMap[$userId];
                } else {
                    $task['assigned_to'] = 'Unassigned';
                }
            }
        }
        return $this->respondWithJson(
            true,
            "Tasks retrieved successfully",
            $tasks
        );
    }

    // function to create new tasks
    public function addTask()
    {
        $input = $this->request->getJSON();
    
        // Log the input data
        log_message('info', "Adding new task with data: " . json_encode($input));
    
        $data = [
            'user_id' => $input->user_id,
            'title' => $input->title,
            'description' => $input->description ?? '',
            'due_date' => $input->due_date ?? null,
            'status' => $input->status,
            'priority' => $input->priority
        ];
    
        try {
            // Start transaction
            $db = \Config\Database::connect();
            $db->transBegin();
            
            if ($this->tasksModel->insert($data)) {
                $taskId = $this->tasksModel->getInsertID();
                $task = $this->tasksModel->find($taskId);
                
                log_message('info', "Task {$taskId} created successfully with user_id: {$task['user_id']}");
    
                // Send notification if a user is assigned and NOT NULL
                if (isset($task['user_id']) && $task['user_id'] > 0) {
                    log_message('info', "Attempting to create notification for new task {$taskId} assigned to user {$task['user_id']}");
                    
                    // Call the notification method explicitly
                    $notificationResult = $this->createTaskNotification($task, 'assignment');
                    
                    if ($notificationResult) {
                        log_message('info', "Successfully created notification for new task {$taskId}");
                    } else {
                        log_message('error', "Failed to create notification for new task {$taskId}");
                    }
                } else {
                    log_message('info', "No notification created for task {$taskId} - no user assigned");
                }

                $db->transCommit();
                return $this->respondWithJson(true, "Task added successfully!", $task);
            } else {
                $errors = $this->tasksModel->errors();
                log_message('error', "Failed to add task: " . json_encode($errors));
                $db->transRollback();
                return $this->respondWithJson(false, "Failed to add task", $errors, 400);
            }
        } catch (\Exception $e) {
            if (isset($db) && $db->transStatus() === false) {
                $db->transRollback();
            }
            log_message('error', "Error adding task: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    // function to assign task to team member
    public function assignTask()
    {
        $input = $this->request->getJSON();

        // Validate required fields
        if (!isset($input->task_id) || !isset($input->user_id)){
            return $this->respondWithJson(false, "Task ID and User ID are required", null, 400);
        }

        // Verify that the task exists
        $task = $this->tasksModel->find($input->task_id);
        if(!$task) {
            return $this->respondWithJson(false, "Task not found", null, 404);
        }

        // Verify that the user exists
        $usersModel = new \App\Models\UsersModel();
        $user = $usersModel->find($input->user_id);
        if (!$user) {
            return $this->respondWithJson(false, "User not found", null, 404);
        }

        // Update task with new user_id
        try {
            if ($this->tasksModel->update($input->task_id, ['user_id' => $input->user_id])){
                // Get updated task
                $updatedTask = $this->tasksModel->find($input->task_id);
                // Send notication to the assigned user
                $this->createTaskNotification($updatedTask, 'assignment');

                return $this->respondWithJson(true, "Task assigned successfully", $updatedTask);
            } else {
                $errors = $this->tasksModel->errors();
                return $this->respondWithJson(false, "Failed to assign task", $errors, 400);
            }
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }

    // function to view task
    public function viewTask($id)
    {
        $task = $this->tasksModel->find($id);

        if(!$task) {
            return $this->respondWithJson(false, "Task not found", null, 404);
        }

        // Get requesting user ID from query parameter
        $requestingUserId = $this->request->getVar('user_id');

        // Add assignment flag to indicate if task is assigned to the requesting user
        $task['isAssignedToYou'] = !empty($requestingUserId) && ((int)$requestingUserId === (int)$task['user_id']);

        $usersModel = new \App\Models\UsersModel();
        $user = $usersModel->find($task['user_id']);
        if ($user) {
            $task['assigned_to'] = $user['name'];
        } else {
            $task['assigned_to'] = 'Unassigned';
        }
        return $this->respondWithJson(true, "Task retrieved successfully", $task);
    }

    // function to get all tasks for a specific user
    public function  getUserTasks ($userId)
    {
        // Convert to integer to ensure type safety
        $userId = (int) $userId;

       // Initialize the query builder with the user_id filter
       $builder = $this->tasksModel->where('user_id', $userId);

       // Get query parameters for filtering
       $priority = $this->request->getGet('priority');
       $status = $this->request->getGet('status');

       // Apply priority filter if provided
       if ($priority) {
        $builder->where('priority', $priority);
       }

       // Apply status filter if provided
       if ($status) {
        $builder->where('status', $status);
       }

       // Execute the query
       $tasks = $builder->findAll();

       return $this->respondWithJson(
        true,
        $tasks ? "Tasks retrieved successfully" : "No tasks found for this user",
        $tasks ?: []
       );
    }

    // function to delete tasks
    public function deleteTask($id)
    {
        if ($this->tasksModel->delete($id)){
            return $this->respondWithJson(true, "Task deleted successfully");
        } else {
            return $this->respondWithJson(false, "Failed to delete tasks", null, 400);
        }
    }
    
    // main edit tasks function
    public function editTask($id)
    {
        $input = $this->request->getJSON();
        $data = [];

        // Get the old task data for comparison
        $oldTask = $this->tasksModel->find($id);
        if (!$oldTask) {
            return $this->respondWithJson(false, "Task not found", null, 404);
        }
        //Check if user assignment is changing
        $userChanged = isset($input->user_id) && $oldTask['user_id'] != $input->user_id;
        $oldUserId = $oldTask['user_id'];
        // Start transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            if (isset($input->user_id)){
                $data['user_id'] = $input->user_id;
            }
    
            if (isset($input->title)){
                $data['title'] = $input->title;
            }
            if (isset($input->description)){
                $data['description'] = $input->description;
            }
            if (isset($input->due_date)){
                $data['due_date'] = $input->due_date;
            }
            if (isset($input->status)){
                $allowedStatuses = ['pending', 'in-progress', 'completed', 'request-extension'];
                if (in_array($input->status, $allowedStatuses)){
                    $data['status'] = $input->status;
                } else {
                    return $this->respondWithJson(
                        false,
                        "Invalid status value. Allowed values are: pending, in-progress, completed, request-extension",
                        null,
                        400
                    );
                }
            }
            if (isset($input->priority)){
                $data['priority'] = $input->priority;
            }
             // Check if user assignment is changing
            $userChanged = isset($data['user_id']) && $oldTask['user_id'] != $data['user_id'];

            // Update the task first
            if ($this->tasksModel->update($id, $data)) {
                $updatedTask = $this->tasksModel->find($id);

                // Only if user assignment changed, send notification
                if ($userChanged) {
                    log_message('info', "User assignment changed for task {$id} from {$oldTask['user_id']} to {$data['user_id']}");

                    // Only process if both old and new users are valid
                    if ($oldUserId > 0 && $input->user_id > 0) {
                        // Notify previous user about reassignment
                        $this->createReassignmentNotification($oldTask, $input->user_id);
                        // Notify new user about assignment
                        $this->createTaskNotification($updatedTask, 'assignment');
                    } else if ($input->user_id > 0) {
                        $this->createTaskNotification($updatedTask, 'assignment');
                    }
                }

                $db->transCommit();
                return $this->respondWithJson(true, "Task updated successfully", $updatedTask);
            } else {
                $errors = $this->tasksModel->errors();
                $db->transRollback();
                return $this->respondWithJson(false, "Failed to update tasks", $errors, 400);
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', "Error updating task: " . $e->getMessage());
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }

    // Updating task status specifically
    public function updateTaskStatus($id)
    {
        $input = $this->request->getJSON();

        if(!isset($input->status)){
            return $this->respondWithJson(false, "Status field is required", null, 400);
        }

        // Validate status against allowed values
        $allowedStatuses = ['pending', 'in-progress', 'completed', 'request-extension'];
        if(!in_array($input->status, $allowedStatuses)){
            return $this->respondWithJson(
                false,
                "Invalid status value. Allowed values are: pending, in-progress, completed, request-extension",
                null,
                400
            );
        }
        // Get requesting user ID
        $requestingUserId = $this->request->getVar('user_id');
        // Get the task to check assignment
        $task = $this->tasksModel->find($id);
        if (!$task) {
            return $this->respondWithJson(false, "Task not found", null, 404);
        }
        // Check if the requesting user is assigned to this task
        if ($requestingUserId && $requestingUserId != $task['user_id']) {
            return $this->respondWithJson(
                false,
                "You cannot update the status of a task that is not assigned to you",
                null,
                403,
            );
        }
        try {
            if ($this->tasksModel->update($id, ['status' => $input->status])){
                $updatedTask = $this->tasksModel->find($id);
                // Add isAssignedToYou if user_id is provided
                if ($requestingUserId) {
                    $updatedTask['isAssignedToYou'] = ($requestingUserId == $updatedTask['user_id']);
                }
                return $this->respondWithJson(true, "Task status updated successfully", $updatedTask);
            } else {
                $errors = $this->tasksModel->errors();
                return $this->respondWithJson(false, "Failed to update task status", $errors, 400);
            }
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }

    // Get available priority levels
    public function getPriorityLevels()
    {
        // Define available priority levels based on validation rules
        $priorityLevels = [
            'low' => [
                'value' => 'low',
                'label' => 'low priority',
                'description' => 'Tasks that are less urgent and can be completed when time allows'
            ],
            'medium' => [
                'value' => 'medium',
                'label' => 'Medium Priority',
                'description' => 'Tasks that should be completed in a timely manner but aren\'t urgent'
            ],
            'high' => [
                'value' => 'high',
                'label' => 'High Priority',
                'description' => 'Urgent tasks that require immediate attention'
            ]
        ];

        return $this->respondWithJson(true, "Priority levels retrieved successfully", $priorityLevels);
    }

    // Update task priority specifically
    public function updateTaskPriority($id)
    {
        $input = $this->request->getJSON();

        if(!isset($input->priority)){
            return $this->respondWithJson(false, "Priority field is required", null, 400);
        }

        // Validate priority against allowed values
        $allowedPriorities = ['low', 'medium', 'high'];
        if(!in_array($input->priority, $allowedPriorities)) {
            return $this->respondWithJson(
                false,
                "Invalid priority value. Allowed values are: low, medium, high",
                null,
                400
            );
        }

        // Get requesting user ID
        $requestingUserId = $this->request->getVar('user_id');

        // Get the task to check assignment
        $task = $this->tasksModel->find($id);
        if (!$task) {
            return $this->respondWithJson(false, "Task not found", null, 404);
        }
        // Check if requesting user is assigned to this task
        if ($requestingUserId && $requestingUserId != $task['user_id']) {
            return $this->respondWithJson(
                false,
                "You cannot update the priority of a task that is not assigned to you",
                null,
                403,
            );
        }
        try {
            if ($this->tasksModel->update($id, ['priority' => $input->priority])){
                $updatedTask = $this->tasksModel->find($id);
                // Add isAssignedToYou flag ig user_id is provided
                if ($requestingUserId) {
                    $updatedTask['isAssignedToYou'] = ($requestingUserId == $updatedTask['user_id']);
                }
                return $this->respondWithJson(true, "Task priority updated successfully", $updatedTask);
            } else {
                $errors = $this->tasksModel->errors();
                return $this->respondWithJson(false, "Failed to update task priority", $errors, 400);
            }
        } catch (\Exception $e){
            return $this->respondWithJson(false, "Internal Server Error",$e->getMessage(), 500);
        }
    }

    // Update task progress percentage
    public function updateTaskProgress($id)
    {
        $input = $this->request->getJSON();

        if (!isset($input->progress)) {
            return $this->respondWithJson(false, "Progress field is required", null, 400);
        }

        // Validate progress value
        $progress = (int) $input->progress;
        if ($progress < 0 || $progress > 100) {
            return $this->respondWithJson(
                false,
                "Progress must be between 0 and 100",
                null,
                400
            );
        }

        // Get requesting user ID
        $requestingUserId = $this->request->getVar('user_id');

        try {
            // Get current task to check if status needs updating
            $task = $this->tasksModel->find($id);
            if(!$task) {
                return $this->respondWithJson(false, "Task not found", null, 404);
            }

            // Check if the requesting user is assigned to this task
            if ($requestingUserId && $requestingUserId != $task['user_id']) {
                return $this->respondWithJson(
                    false,
                    "You cannot update the progress of a task that is not assigned to you",
                    null,
                    403
                );
            }

            $data = ['progress' => $progress];

            // Auto-update status based on progress if needed
            if($progress == 100 && $task['status'] != 'completed') {
                $data['status'] = 'completed';
            } elseif ($progress > 0 && $progress < 100 && $task['status'] == 'pending') {
                $data['status'] = 'in-progress';
            }

            if ($this->tasksModel->update($id, $data)) {
                $updatedTask = $this->tasksModel->find($id);
                // Add isAssignedToYou flag if user_id is provided
                if ($requestingUserId) {
                    $updatedTask['isAssignedToYou'] = ($requestingUserId == $updatedTask['user_id']);
                }
                return $this->respondWithJson(true, "Task progress updated successfully", $updatedTask);
            } else {
                $errors = $this->tasksModel->errors();
                return $this->respondWithJson(false, "Failed to update task progress", $errors, 400);
            }
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }

    // Get task completion statistics
    public function getTaskCompletionStats()
    {
        $db = \Config\Database::connect();

        // Get statistics parameters
        $userId = $this->request->getGet('user_id');
        $teamId = $this->request->getGet('team_id');
        $period = $this->request->getGet('period'); // 'week', 'month', 'all'

        try {
            $stats = [];

            // 1. Overall task completion rate
            $builder = $db->table('tasks');
            $builder->selectCount('id', 'total');
            $builder->selectCount('id', 'completed')->where('status', 'completed');
            $builder->selectAvg('progress', 'average_progress');

            // Apply filters if provided
            if ($userId) {
                $builder->where('user_id', $userId);
            }

            if ($teamId) {
                $subquery = $db->table('users')
                    ->select('id')
                    ->where('team_id', $teamId);
                $builder->whereIn('user_id', $subquery);
            }

            if ($period == 'week') {
                $builder->where('created_at >=', date('Y-m-d', strtotime('-7 days')));
            } elseif ($period == 'month') {
                $builder->where('created_at >=', date('Y-m-d', strtotime('-30 days')));
            }

            $result = $builder->get()->getRowArray();

            // Calculate the completion rate
            $completionRate = 0;
            if ($result['total'] > 0) {
                $completionRate = round(($result['completed'] / $result['total']) * 100, 2);
            }

            $stats['overall'] = [
                'total_tasks' => (int)$result['total'],
                'completed_tasks' => (int)$result['completed'],
                'completion_rate' => $completionRate,
                'average_progress' => round((float)$result['average_progress'], 2),
            ];

            // 2. Progress breakdown by status
            $builder = $db->table('tasks');
            $builder->select('status, COUNT(*) as count, AVG(progress) as avg_progress');
            $builder->groupBy('status');

            // Apply the same filters
            if ($userId) {
                $builder->where('user_id', $userId);
            }

            if ($teamId) {
                $subquery = $db->table('users')
                    ->select('id')
                    ->where('team_id', $teamId);
                $builder->whereIn('user_id', $subquery);
            }

            if ($period == 'week') {
                $builder->where('created_at >=', date('Y-m-d', strtotime('-7 days')));
            } elseif ($period == 'month') {
                $builder->where('created_at >=', date('Y-m-d', strtotime('-30 days')));
            }

            $statusBreakdown = $builder->get()->getResultArray();
            $stats['status_breakdown'] = $statusBreakdown;

            // 3. Progress breakdown by priority
            $builder = $db->table('tasks');
            $builder->select('priority, COUNT(*) as count, AVG(progress) as avg_progress');
            $builder->groupBy('priority');

            // Apply the same filters
            if ($userId) {
                $builder->where('user_id', $userId);
            }

            if ($teamId) {
                $subquery = $db->table('users')
                    ->select('id')
                    ->where('team_id', $teamId);
                $builder->whereIn('user_id', $subquery);
            }

            if ($period == 'week') {
                $builder->where('created_at >=', date('Y-m-d', strtotime('-7 days')));
            } elseif ($period == 'month') {
                $builder->where('created_at >=', date('Y-m-d', strtotime('-30 days')));
            }

            $priorityBreakdown = $builder->get()->getResultArray();
            $stats['priority_breakdown'] = $priorityBreakdown;

            // Return the statistics
            return $this->respondWithJson(true, "Task completion statistic retrieved successfully", $stats);
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    private function createReassignmentNotification($oldTask, $newUserId)
    {
        try {
            // Get user data
            $usersModel = new \App\Models\UsersModel();
            $oldUser = $usersModel->find($oldTask['user_id']);
            $newUser = $usersModel->find($newUserId);

            if (!$oldUser) {
                log_message('error', "Cannot send reassignment notification: Old user {$oldTask['user_id']} not found");
                return false;
            }

            // Get new user name for the message
            $newUserName = $newUser ? $newUser['name'] : 'another user';

            // Setup notification data
            $title = "Task Reassigned";
            $message = "Task '{$oldTask['title']}' has been reassigned to {$newUserName}.";

            // Start transaction
            $db = \Config\Database::connect();
            $db->transBegin();

            // Check for existing notifications for this task and user
            $notificationsModel = new \App\Models\NotificationsModel();
            $existingNotification = $notificationsModel->where('user_id', $oldUser['id'])
                ->where('task_id', $oldTask['id'])
                ->where('title', 'Task Assigned') // Look for the original assignment notification
                ->orderBy('id', 'DESC')
                ->first();

            $notificationId = null;

            if ($existingNotification) {
                // Update the existing notification instead of creating a new one
                $notificationsModel->update($existingNotification['id'], [
                    'title' => $title,
                    'message' => $message,
                    'is_read' => false, // Mark as unread since it's a new update
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                $notificationId = $existingNotification['id'];
                log_message('info', "Updated existing notification {$notificationId} for user {$oldUser['id']} about task reassignment");
            } else {
                // Create a new notification if no existing one found
                $notificationData = [
                    'user_id' => $oldUser['id'],
                    'task_id' => $oldTask['id'],
                    'title' => $title,
                    'message' => $message,
                    'is_read' => false
                ];

                $result = $notificationsModel->insert($notificationData);
                if (!$result) {
                    log_message('error', "Reassignment notification not created. Validation errors: " . json_encode($notificationsModel->errors()));
                    $db->transRollback();
                    return false;
                }

                $notificationId = $notificationsModel->getInsertID();
                log_message('info', "Created new notification {$notificationId} for user {$oldUser['id']} about task reassignment");
            }

            // Get the notification
            $notification = $notificationsModel->find($notificationId);

            // Send to notification server
            $sent = $this->sendToNotificationServer($oldTask, $notificationId);

            if (!$sent) {
                log_message('warning', "Created/updated notification {$notificationId} but failed to send to real-time server");
            }

            // Commit transaction
            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            // Ensure transaction is rolled back
            if (isset($db) && $db->transStatus() === false) {
                $db->transRollback();
            }
            log_message('error', "Failed to create reassignment notification: " . $e->getMessage());
            return false;
        }
    }

    private function notifyTaskAssignment($task)
    {
        return $this->createTaskNotification($task, 'assignment');
    }
    // Send notification when a task is assigned to a user
    private function sendTaskAssignmentNotification($task)
    {
        return $this->createTaskNotification($task, 'assignment');
    }
    private function sendToNotificationServer($task, $notificationId = null)
    {
        try {
            // Need to find the notification and the user
            $notificationsModel = new \App\Models\NotificationsModel();
            $usersModel = new \App\Models\UsersModel();

            // Get the notification
            $notification = null;
            if ($notificationId) {
                $notification = $notificationsModel->find($notificationId);
                if (!$notification) {
                    log_message('error', "Cannot send notification {$notificationId} - not found in database");
                    return false;
                }
            } else {
                $notification = $notificationsModel->where('user_id', $task['user_id'])
                    ->where('task_id', $task['id'])
                    ->orderBy('id', 'DESC')
                    ->first();

                if (!$notification) {
                    log_message('error', "No notification found for task {$task['id']}, user {$task['user_id']}");
                    return false;
                }
            }

            // Get the user data
            $user = $usersModel->find($notification['user_id']);
            if (!$user) {
                log_message('error', "User {$notification['user_id']} not found for notification {$notification['id']}");
                return false;
            }
    
            // Get notification server URL from environment
            $notificationServerUrl = getenv('NOTIFICATION_SERVER_URL');
            if (empty($notificationServerUrl)) {
                $notificationServerUrl = 'http://localhost:3000'; // Default fallback
            }

            log_message('info', "Sending notification {$notification['id']} to server at {$notificationServerUrl}");

            // Log the request payload for debugging
            $payload = [
                'notification_id' => (int)$notification['id'],
                'user_id' => (int)$notification['user_id'],
                'task_id' => (int)$notification['task_id'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'is_read' => false
            ];

            log_message('debug', "Notification server payload: " . json_encode($payload));

            // Send to notification server with explicit notification ID
            $client = \Config\Services::curlrequest();
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

            // Log the success response
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody(), true);

            log_message('info', "Notification server response for notification {$notification['id']}: HTTP {$statusCode}, Body: " . json_encode($responseBody));

            return $statusCode >= 200 && $statusCode < 300;
        } catch (\Exception $e) {
            log_message('error', "Failed to send notification to server: " . $e->getMessage());
            return false;
        }
    }
    // Centralize Notification Creation
    private function createTaskNotification($task, $notificationType)
    {
        try {
            // Get user data
            $usersModel = new \App\Models\UsersModel();
            $user = $usersModel->find($task['user_id']);

            if (!$user) {
                log_message('error', "Cannot send notification: User {$task['user_id']} not found");
                return false;
            }

            // Setup notification data based on type
            $title = "";
            $message = "";

            switch ($notificationType) {
                case 'assignment':
                    $title = "Task Assigned";
                    $message = "You have been assigned to task: {$task['title']}. Please check your tasks for details.";
                    break;
                case 'update':
                    $title = "Task Updated";
                    $message = "Task '{$task['title']}' has been updated. Please check for changes.";
                    break;

            }

            // Start transaction
            $db = \Config\Database::connect();
            $db->transBegin();

            // Generate a unique signature for this notification to prevent duplicates
            $notificationSignature = md5($user['id'] . $task['id'] . $title . $notificationType);

            // Check if we've recently created this exact notification (within 15 seconds)
            $notificationsModel = new \App\Models\NotificationsModel();
            $existingNotification = $notificationsModel->where('user_id', $user['id'])
                ->where('task_id', $task['id'])
                ->where('title', $title)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-15 seconds')))
                ->first();

            if ($existingNotification) {
                log_message('info', "Duplicate notification prevented for user {$user['id']} and task {$task['id']} (found existing ID: {$existingNotification['id']})");
                $db->transRollback();
                return true;
            }

            // Create notification data
            $notificationData = [
                'user_id' => $user['id'],
                'task_id' => $task['id'],
                'title' => $title,
                'message' => $message,
                'is_read' => false
            ];

            // Insert notification
            $result = $notificationsModel->insert($notificationData);
            if (!$result) {
                log_message('error', "Notification not created. Validation errors: " . json_encode($notificationsModel->errors()));
                $db->transRollback();
                return false;
            }

            // Get the new notification ID
            $notificationId = $notificationsModel->getInsertID();

            // Log the creation
            log_message('info', "Notification {$notificationId} created for user {$user['id']} for task {$task['id']} of type {$notificationType}");

            // Send to notification server with specific notification ID
            $sent = $this->sendToNotificationServer($task, $notificationId);

            if (!$sent) {
                log_message('warning', "Created notification {$notificationId} but failed to send to real-time server");
            }

            // Commit transaction
            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            // Ensure transaction is rolled back
            if (isset($db) && $db->transStatus() === false) {
                $db->transRollback();
            }
            log_message('error', "Failed to create task notification: " . $e->getMessage());
            return false;
        }
    }
    private function respondWithJson($status, $msg, $data = null, $statusCode=200)
    {
    $response = [
        "status" => $status,
        "msg" => $msg
    ];

    if($data !== null){
        $response['data'] = $data;
    }

    return $this->response->setJSON($response)->setStatusCode($statusCode);
    }
}
