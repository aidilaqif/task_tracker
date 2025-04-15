<?php

namespace App\Controllers;

class WebUIController extends BaseController
{
    // Main view
    public function index(): string
    {
        return view('sidebar');
    }
    // Dashboard view
    public function dashboard()
    {
        $data['active_menu'] = 'dashboard';
        return view('dashboard', $data);
    }
     // Team view
     public function team()
     {
        $data['active_menu'] = 'team';
         return view('team', $data);
     }
      // Task view
    public function task()
    {
        $data['active_menu'] = 'task';
        return view('task', $data);
    }
    // Team Detail view
    public function teamDetail()
    {
        $data['active_menu'] = 'team';
        return view('team_detail', $data);
    }
    // Task Detail View
    public function taskDetail()
    {
        $data['active_menu'] = 'task';
        return view('task_detail', $data);
    }
    // Check database connection
    public function connection(): void
    {
        try {
            $db = \Config\Database::connect();
            
            // Add more detailed error checking
            if ($db === false) {
                echo "Database connection failed completely.";
                
                // Get last error from database configuration
                $error = \Config\Database::connect()->error();
                print_r($error);
                
                // Check specific connection details
                $config = config('Database')->default;
                echo "\nHostname: " . $config['hostname'];
                echo "\nUsername: " . $config['username'];
                echo "\nDatabase: " . $config['database'];
                echo "\nPort: " . $config['port'];
            } else {
                echo "Database connection successful.";
            }
        } catch (\Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
    }
}
