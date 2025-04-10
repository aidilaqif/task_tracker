<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }
    // public function connection(): void
    // {
    //     $db = \Config\Database::connect();
    //     if ($db->connID) {
    //         echo "Database connection successful.";
    //     } else {
    //         echo "Database connection failed.";

    //     }
    // }
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
