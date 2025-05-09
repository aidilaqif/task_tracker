<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;

class UsersController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }
    
    // New method: Get all users with pagination and filtering
    public function getAllUsers()
    {
        // Get query parameters for filtering
        $search = $this->request->getGet('search');
        $role = $this->request->getGet('role');
        $teamId = $this->request->getGet('team_id');
        
        // Get pagination parameters
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 20);
        
        // Start building the query
        $builder = $this->usersModel->builder();
        
        // Apply search filter if provided
        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }
        
        // Apply role filter if provided
        if ($role) {
            $builder->where('role', $role);
        }
        
        // Apply team filter if provided
        if ($teamId) {
            $builder->where('team_id', $teamId);
        }
        
        // Get total count for pagination
        $total = $builder->countAllResults(false);
        
        // Get paginated results
        $users = $builder->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();
        
        // Remove passwords from user data
        foreach ($users as &$user) {
            unset($user['password']);
        }
        
        // Prepare pagination info
        $pagination = [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ];
        
        return $this->respondWithJson(
            true, 
            "Users retrieved successfully", 
            [
                'users' => $users,
                'pagination' => $pagination
            ]
        );
    }
    
    // New method: Get a specific user by ID
    public function getUser($id)
    {
        $user = $this->usersModel->find($id);
        
        if (!$user) {
            return $this->respondWithJson(false, "User not found", null, 404);
        }
        
        // Remove sensitive information
        unset($user['password']);
        
        return $this->respondWithJson(true, "User retrieved successfully", $user);
    }
    
    // New method: Update a user's details
    public function updateUser($id)
    {
        $input = $this->request->getJSON();
        
        // Check if user exists
        $user = $this->usersModel->find($id);
        if (!$user) {
            return $this->respondWithJson(false, "User not found", null, 404);
        }
        
        // Prepare data for update
        $data = [];
        
        if (isset($input->name)) {
            $data['name'] = $input->name;
        }
        
        if (isset($input->email)) {
            // Check if email is already in use by another user
            $existingUser = $this->usersModel->where('email', $input->email)->first();
            if ($existingUser && $existingUser['id'] != $id) {
                return $this->respondWithJson(false, "Email is already in use", null, 400);
            }
            $data['email'] = $input->email;
        }
        
        if (isset($input->role)) {
            $data['role'] = $input->role;
        }
        
        if (isset($input->team_id)) {
            $data['team_id'] = $input->team_id;
        }
        
        // Update password if provided
        if (isset($input->password) && !empty($input->password)) {
            $data['password'] = password_hash($input->password, PASSWORD_DEFAULT);
        }
        
        // If no data to update
        if (empty($data)) {
            return $this->respondWithJson(false, "No data provided for update", null, 400);
        }
        
        try {
            if ($this->usersModel->update($id, $data)) {
                // Get updated user
                $updatedUser = $this->usersModel->find($id);
                unset($updatedUser['password']);
                
                return $this->respondWithJson(true, "User updated successfully", $updatedUser);
            } else {
                $errors = $this->usersModel->errors();
                return $this->respondWithJson(false, "Failed to update user", $errors, 400);
            }
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    
    // New method: Delete a user
    public function deleteUser($id)
    {
        // Check if user exists
        $user = $this->usersModel->find($id);
        if (!$user) {
            return $this->respondWithJson(false, "User not found", null, 404);
        }
        
        try {
            if ($this->usersModel->delete($id)) {
                return $this->respondWithJson(true, "User deleted successfully");
            } else {
                $errors = $this->usersModel->errors();
                return $this->respondWithJson(false, "Failed to delete user", $errors, 400);
            }
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    
    // User register function
    public function addUser()
    {
    $input = $this->request->getJSON();

    if($this->isEmailExists($input->email)){
        return $this->respondWithJson(false, "Email is already in use!");
    }

    $data = [
        'name' => $input->name,
        'email' => $input->email,
        'password' => password_hash($input->password, PASSWORD_DEFAULT),
        'role' => $input->role ?? 'user',
        'team_id' => $input->team_id ?? null
    ];

    return $this->insertUser($data);
    }
    // User login function
    public function login()
    {
    $input = $this->request->getJSON();

    $user = $this->usersModel->where('email', $input->email)->first();

    if ($user && password_verify($input->password, $user['password'])){
        unset($user['password']); // remove sensitive data

        // Response object with explicit role information
        $responseData = [
            'user' => $user,
            'role' => $user['role'],
            'permissions' => $this->getRolePermissions($user['role'])
        ];

        return $this->respondWithJson(true, "Login successful", $responseData);
    }

    return $this->respondWithJson(false, "Invalid Username or Password");
    }
    // Get users by their team ID
    public function getUsersByTeam($teamId)
    {
        $users = $this->usersModel->where('team_id', $teamId)->findAll();

        if(!$users) {
            return $this->respondWithJson(true, "No user found in this team", []);
        }

        // Remove password from user data
        foreach ($users as &$user) {
            unset($user['password']);
        }

        return $this->respondWithJson(true, "Users retrieved successfully", $users);
    }
    // Update user's team assignment
    public function updateUserTeam()
    {
        $input = $this->request->getJSON();

        if(!isset($input->user_id) || !isset($input->team_id)){
            return $this->respondWithJson(false, "User ID and Team ID are required", null, 400);
        }

        $user = $this->usersModel->find($input->user_id);
        if(!$user){
            return $this->respondWithJson(false, "User not found", null,404);
        }

        try {
            if ($this->usersModel->update($input->user_id, ['team_id' => $input->team_id])) {
                return $this->respondWithJson(true, "User team updated successfully");
            } else {
                $errors = $this->usersModel->errors();
                return $this->respondWithJson(false, "Failed to update user team", $errors, 400);
            }
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    // Remove a user from any team
    public function removeUserFromTeam($userId){
        $user = $this->usersModel->find($userId);

        if(!$user) {
            return $this->respondWithJson(false, "User not found", null, 404);
        }

        try {
            if ($this->usersModel->update($userId, ['team_id' => null])) {
                return $this->respondWithJson(true, "User removed from team successfully");
            } else {
                $errors = $this->usersModel->errors();
                return $this->respondWithJson(false, "Failed to remove user from team", 400);
            }
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    // Get permission based on user role (determine what frontend feature to show)
    private function getRolePermissions($role)
    {
        $permissions = [];

        switch($role){
            case 'admin':
                $permissions = [
                    'canCreateUser' => true,
                    'canDeleteUser' => true,
                    'canAssignTasks' => true,
                    'canDeleteTasks' => true,
                    'canViewAllTasks' => true,
                    'canSetPriorities' => true,
                    'canGenerateReports' => true
                ];
                break;
            case 'user':
            default:
            $permissions = [
                'canCreateUser' => false,
                'canDeleteUser' => false,
                'canAssignTasks' => false,
                'canDeleteTasks' => false,
                'canViewAllTasks' => false,
                'canSetPriorities' => false,
                'canGenerateReports' => false
            ];
            break;
        }
        return $permissions;
    }
    // Get users without team
    public function getUsersWithoutTeam()
    {
        $users = $this->usersModel->getUsersWithoutTeam();

        // Remove password from user data
        foreach ($users as &$user) {
            unset($user['password']);
        }

        return $this->respondWithJson(
            true,
            "Users without team retrieved successfully",
            $users
        );
    }
    // User Logout function
    public function logout()
    {
        return $this->respondWithJson(true, "Logged out successfully");
    }

    private function isEmailExists($email)
    {
    return $this->usersModel->where('email', $email)->first() !== null;
    }

    private function insertUser($data)
    {
        try{
            if ($this->usersModel->insert($data)){
                return $this->respondWithJson(true, "Registered Successfully");
            }

            $errors = $this->usersModel->errors();
            log_message('error', 'User registration failed: '.json_encode($errors));
            return $this->respondWithJson(false, "Registration failed", $errors, 400);
        } catch (\Exception $e){
            log_message('error', 'Exception: '.$e->getMessage());
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }


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