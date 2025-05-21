<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => '1',
                'name' => "Aidil 'Aqif",
                'email' => 'aidil@gmail.com',
                'password' => '$2y$10$QOhm56QoQ2jq7mkegjzu0eLSTntGaMjo3P.Sqom3d6pLgmr9KXPq6',
                'role' => 'admin',
                'team_id' => null
            ],
            [
                'id' => '2',
                'name' => 'Ainul',
                'email' => 'ainul@gmail.com',
                'password' => '$2y$10$pMv.qtMUSPyf8kREr6fySOe67VdgWhIkTa5WUpEJmFPajIDtl5pPe',
                'role' => 'user',
                'team_id' => '4'
            ],
            [
                'id' => '3',
                'name' => 'Hafiz',
                'email' => 'hafiz@gmail.com',
                'password' => '$2y$10$kbSTh7H4s/YAPGygkIGS/eFPLGhhAw8qTyMIrfRK3glNgBXdsKdtK',
                'role' => 'user',
                'team_id' => '4'
            ],
            [
                'id' => '4',
                'name' => 'Mohammad Hafizzudin',
                'email' => 'hafizzudin@gmail.com',
                'password' => '$2y$10$qQqaSFGHLmZ3FZMkoxDLS.pYhpUSibNYH/WhcE3bdP39L9fwTn/r.',
                'role' => 'user',
                'team_id' => '1'
            ],
            [
                'id' => '5',
                'name' => 'Muhamad Nur Hadi',
                'email' => 'hadi@gmail.com',
                'password' => '$2y$10$1VxRhfv/HN7c9Fpp6Bi4Ie/PAjHea1lexFjQB0AbxSqWPXuJkP4BW',
                'role' => 'user',
                'team_id' => '2'
            ],
            [
                'id' => '6',
                'name' => 'Sabri Hilal',
                'email' => 'sabri@gmail.com',
                'password' => '$2y$10$oAiVnfQDQWhJcQLJ/K.AxOwRXhXX3j3scRwXh1xI3YFGLpFLXazpW',
                'role' => 'user',
                'team_id' => '3'
            ],
            [
                'id' => '7',
                'name' => 'Muhammad Nabil',
                'email' => 'nabil@gmail.com',
                'password' => '$2y$10$SaDjiFANTRNvWeOeoeYcnOSJIESg5y./cT9jnOStIB0gIpg7je4pC',
                'role' => 'user',
                'team_id' => '3'
            ],
            [
                'id' => '8',
                'name' => "Aidil 'Aqif",
                'email' => 'aidil.aqif@gmail.com',
                'password' => '$2y$10$gxBDTRIdrXhDl3INs.aPNeLderE9bZ1kSvXaNb3i6JGk7iSXMbZO6',
                'role' => 'user',
                'team_id' => '4'
            ],
            [
                'id' => '9',
                'name' => 'Rohani',
                'email' => 'rohani@gmail.com',
                'password' => '$2y$10$6UcqAalE6yueqa2XIFIIU.JAuGxvS4WLR3wcr4rQVZqBCwQBrtk9.',
                'role' => 'user',
                'team_id' => '1'
            ],
            [
                'id' => '10',
                'name' => 'Nur Asyikin',
                'email' => 'asyikin@gmail.com',
                'password' => '$2y$10$huK6lAmISe5CZvUEl6gEMu6LBz6iQ2dgpwk583OwP0E.WhbnK5X/i',
                'role' => 'user',
                'team_id' => '1'
            ],
            [
                'id' => '11',
                'name' => 'Norzaiha Azwa',
                'email' => 'norzaiha@gmail.com',
                'password' => '$2y$10$PVxBnYamJQJrRbPDPRzd/eKL66eKJyu.TVhRIQyBzHDqogW5YRVka',
                'role' => 'user',
                'team_id' => '1'
            ],
            [
                'id' => '12',
                'name' => 'Fatin Nur Fadzilah',
                'email' => 'fatin@gmail.com',
                'password' => '$2y$10$zkxL4fprUjSCn3jsJ9DLEe1r5VKxZwJttptYhE5m7jgz5UQ25Wh8u',
                'role' => 'user',
                'team_id' => '1'
            ],
            [
                'id' => '13',
                'name' => 'Norfarhanim Shamimi',
                'email' => 'norfarhanim@gmail.com',
                'password' => '$2y$10$u.gPoAqeGfvlKnJB1dVFT.7eaz7.0c2wxhFA7YmVCp0a04.Td8g3S',
                'role' => 'user',
                'team_id' => '1'
            ],
            [
                'id' => '14',
                'name' => 'Nur Farra Ain',
                'email' => 'farra@gmail.com',
                'password' => '$2y$10$mMd3w8uD5f8nSf8J6/bGy.1n/4rCpMrsEPTJ1TOtAMOp4RoFBa2sq',
                'role' => 'user',
                'team_id' => '5'
            ],
            [
                'id' => '15',
                'name' => 'Nur Shazwani Izzati',
                'email' => 'wani@gmail.com',
                'password' => '$2y$10$oORih386vrKJhOtXBg.n0uSC9iFvpLxV0ueshAZkvwcJSV4UQRSzW',
                'role' => 'user',
                'team_id' => '5'
            ],
            [
                'id' => '16',
                'name' => 'Nurshazreena',
                'email' => 'reena@gmail.com',
                'password' => '$2y$10$ALVhAj9faxlr.f1TDE5G3OmVtVZ6h4uRRvRg9cKX8AcBf8YLi2UMG',
                'role' => 'user',
                'team_id' => '5'
            ],
        ];

        // Using Query Builder
        $this->db->table('users')->insertBatch($data);
    }
}