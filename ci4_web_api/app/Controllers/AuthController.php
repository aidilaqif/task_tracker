<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    // Login page view
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        
        return view('auth/login');
    }
    
    // Set session data from API response
    public function setSession()
    {
        $userData = $this->request->getJSON();
        
        // Validate that we have user data
        if (!isset($userData->user) || !isset($userData->role)) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Invalid user data'
            ]);
        }
        
        // Check if user is admin
        if ($userData->role !== 'admin') {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Access denied. Only administrators can access this system.'
            ]);
        }
        
        // Set session data
        $sessionData = [
            'user_id' => $userData->user->id,
            'name' => $userData->user->name,
            'email' => $userData->user->email,
            'role' => $userData->role,
            'isLoggedIn' => true
        ];
        
        session()->set($sessionData);
        
        return $this->response->setJSON([
            'status' => true,
            'msg' => 'Session created successfully'
        ]);
    }
    
    // Logout function
    public function logout()
    {
        // Clear session data
        session()->destroy();
        
        return redirect()->to('/login');
    }
}