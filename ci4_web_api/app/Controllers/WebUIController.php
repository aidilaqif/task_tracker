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
        return view('dashboard');
    }
     // Team view
     public function team()
     {
         return view('team');
     }
      // Task view
    public function task()
    {
        return view('task');
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
