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

        $data = [
            'user_id' => $input->user_id,
            'title' => $input->title,
            'description' => $input->description ?? '',
            'due_date' => $input->due_date ?? null,
            'status' => $input->status,
            'priority' => $input->priority
        ];

        try {
            if ($this->tasksModel->insert($data)){
                return $this->respondWithJson(true, "Task added successfully!");
            } else {
                $errors = $this->tasksModel->errors();
                return $this->respondWithJson(false, "Failed to add task", $errors, 400);
            }
        } catch (\Exception $e) {
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
                // $this->sendNewTaskNotification($user, $updatedTask);
                try {
                    $notificationsModel = new \App\Models\NotificationsModel();
                    $notificationData = [
                        'user_id' => $user['id'],
                        'task_id' => $task['id'],
                        'title' => 'New Task Assigned',
                        'message' => "You have been assigned a new task: {$task['title']}. Please check your tasks list for details.",
                        'is_read' => false
                    ];

                    $result = $notificationsModel->insert($notificationData);
                    if (!$result) {
                        log_message('error', "Notification not created. Validation errors: ".json_encode($notificationsModel->errors()));
                    } else {
                        log_message('info', "Notification created for user {$user['id']} for task {$task['id']}");
                    }
                } catch (\Exception $e) {
                    log_message('error', "Failed to create notification: ". $e->getMessage());
                }

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

        if ($task) {
            $usersModel = new \App\Models\UsersModel();
            $user = $usersModel->find($task['user_id']);
            if ($user) {
                $task['assigned_to'] = $user['name'];
            } else {
                $task['assigned_to'] = 'Unassigned';
            }
            return $this->respondWithJson(true, "Task retrieved successfully", $task);
        } else {
            return $this->respondWithJson(false, "Tasks not found", null, 404);
        }
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

        try{
            if ($this->tasksModel->update($id, $data)){
                $updatedTask = $this->tasksModel->find($id);
                return $this->respondWithJson(true, "Task updated successfully", $updatedTask);
            } else {
                $errors = $this->tasksModel->errors();
                return $this->respondWithJson(false, "Failed to update tasks", $errors, 400);
            }
        }catch (\Exception $e){
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
        try {
            if ($this->tasksModel->update($id, ['status' => $input->status])){
                $updatedTask = $this->tasksModel->find($id);
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
        try {
            if ($this->tasksModel->update($id, ['priority' => $input->priority])){
                $updatedTask = $this->tasksModel->find($id);
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

        try {
            // Get current task to check if status needs updating
            $task = $this->tasksModel->find($id);
            if(!$task) {
                return $this->respondWithJson(false, "Task not found", null, 404);
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
    // Send new notification to user
    // private function sendNewTaskNotification($user, $task)
    // {
    //     // Prepare notification data
    //     $notificationData = [
    //         'user_id' => $user['id'],
    //         'task_id' => $task['id'],
    //         'title' => 'New Task Assigned',
    //         'message' => "You have been assigned a new task: {$task['title']}. Please check your tasks list for details.",
    //         'is_read' => false
    //     ];

    //     // Insert notification
    //     try {
    //         $this->notificationsModel->insert($notificationData);
    //         log_message('info', "Notification sent to user {$user['id']} for task {$task['id']}");
    //     } catch (\Exception $e) {
    //         // Log the error
    //         log_message('error', "Failed to send notification: ".$e->getMessage());
    //     }
    // }
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
