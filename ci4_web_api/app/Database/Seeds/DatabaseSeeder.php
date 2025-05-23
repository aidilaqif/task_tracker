<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Run seeders in order to handle dependencies
        $this->call('TeamsSeeder');
        $this->call('UsersSeeder');
        $this->call('TasksSeeder');
        $this->call('TeamMembersSeeder');
        $this->call('NotificationsSeeder');
    }
}