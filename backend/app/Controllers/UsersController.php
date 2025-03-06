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
        'password' => $input->password,
        'role' => 'user'
    ];

    return $this->insertUser($data);
    }

    public function login()
    {
    $input = $this->request->getJSON();
    $user = $this->usersModel->where('email', $input->email)->where('password', $input->password)->first();

    if ($user){
        unset($user['password']); // remove sensitive data
        return $this->respondWithJson(true, "Login successful", $user);
    }

    return $this->respondWithJson(false, "Invalid Username or Password");
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
