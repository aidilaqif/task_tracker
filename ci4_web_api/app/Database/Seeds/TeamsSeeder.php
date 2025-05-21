<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TeamsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => '1',
                'name' => 'Software Development',
                'description' => '',
                'created_at' => '2025-05-14 02:29:18',
                'updated_at' => '2025-05-14 02:30:00'
            ],
            [
                'id' => '2',
                'name' => 'Infrastructure',
                'description' => '',
                'created_at' => '2025-05-14 02:29:50',
                'updated_at' => '2025-05-14 02:29:50'
            ],
            [
                'id' => '3',
                'name' => 'Data',
                'description' => '',
                'created_at' => '2025-05-14 02:30:12',
                'updated_at' => '2025-05-14 02:30:12'
            ],
            [
                'id' => '4',
                'name' => 'Intern',
                'description' => '',
                'created_at' => '2025-05-14 02:30:20',
                'updated_at' => '2025-05-14 02:30:20'
            ],
            [
                'id' => '5',
                'name' => 'Usability',
                'description' => '',
                'created_at' => '2025-05-14 02:37:26',
                'updated_at' => '2025-05-14 02:37:26'
            ],
        ];

        // Using Query Builder
        $this->db->table('teams')->insertBatch($data);
    }
}