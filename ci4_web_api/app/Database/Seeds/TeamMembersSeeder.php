<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TeamMembersSeeder extends Seeder
{
    public function run()
    {
        // The team_members table is empty in the provided JSON data
        // This seeder is kept for consistency, but won't insert any data
        
        $data = [];
        
        // If we need to add team members later, add the data here
        
        // Using Query Builder - only if we have data
        if (!empty($data)) {
            $this->db->table('team_members')->insertBatch($data);
        }
    }
}