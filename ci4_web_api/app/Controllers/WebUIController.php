<?php

namespace App\Controllers;

class WebUIController extends BaseController
{
    // Constructor to ensure session is active
    public function __construct()
    {
        // The auth filter will already check for authentication
        // This is just an extra safety check
        if (!session()->get('isLoggedIn')) {
            // This should never be executed due to the auth filter,
            // but it's a good safety measure
            return redirect()->to('/login');
        }
    }
    
    // Dashboard view
    public function dashboard()
    {
        $data['active_menu'] = 'dashboard';
        $data['css_files'] = ['dashboard'];
        $data['title'] = 'Dashboard';
        
        // Pass user data to the view if needed
        $data['user'] = [
            'name' => session()->get('name'),
            'email' => session()->get('email'),
            'role' => session()->get('role')
        ];
        
        return view('dashboard/dashboard', $data);
    }
    
    // Team view
    public function team()
    {
        $data['active_menu'] = 'team';
        $data['css_files'] = ['team'];
        $data['title'] = 'Team Management';
        
        return view('team/team', $data);
    }
    
    // Task view
    public function task()
    {
        $data['active_menu'] = 'task';
        $data['css_files'] = ['task'];
        $data['title'] = 'Task Management';
        
        return view('task/task', $data);
    }
    
    // Team Detail view
    public function teamDetail()
    {
        $data['active_menu'] = 'team';
        $data['css_files'] = ['team_detail'];
        $data['title'] = 'Team Details';
        
        return view('team_detail/team_detail', $data);
    }
    
    // Task Detail View
    public function taskDetail()
    {
        $data['active_menu'] = 'task';
        $data['css_files'] = ['task_detail'];
        $data['title'] = 'Task Details';
        
        return view('task_detail/task_detail', $data);
    }
    
    // User Management View
    public function user()
    {
        $data['active_menu'] = 'user';
        $data['css_files'] = ['user'];
        $data['title'] = 'User Management';
        
        return view('user/user', $data);
    }
}