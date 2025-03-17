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
        'role' => 'user'
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
