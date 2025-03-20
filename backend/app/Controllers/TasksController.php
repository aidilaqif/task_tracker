<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TasksModel;
use App\Models\UsersModel;
// use CodeIgniter\HTTP\ResponseInterface;

class TasksController extends BaseController
{
    protected $tasksModel;

    public function __construct()
    {
        $this->tasksModel = new TasksModel();
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
