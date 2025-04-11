<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class AdminController extends BaseController
{
    /**
     * Dashboard page - default landing page
     */
    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard',
            'header' => 'Dashboard',
            'active_menu' => 'dashboard'
        ];
        
        return view('admin/dashboard', $data);
    }
    
    /**
     * Team management page
     */
    public function team()
    {
        $data = [
            'title' => 'Team Management',
            'header' => 'Team Management',
            'active_menu' => 'team'
        ];
        
        return view('admin/team', $data);
    }
    
    /**
     * Task management page
     */
    public function tasks()
    {
        $data = [
            'title' => 'Task Management',
            'header' => 'Task Management',
            'active_menu' => 'tasks'
        ];
        
        return view('admin/tasks', $data);
    }
    
    /**
     * Logout function (just redirects to login page for now)
     */
    public function logout()
    {
        // Destroy the web session
        session()->destroy();

        // Set a flash message to show on the login page
        session()->setFlashdata('message', 'You have been successfully logged out.');

        // Redirect to login page
        return redirect()->to(site_url('login'));
    }
}