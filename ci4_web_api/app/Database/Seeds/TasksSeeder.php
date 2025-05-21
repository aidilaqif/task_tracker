<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TasksSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => '1',
                'user_id' => '4',
                'title' => 'Implement User Authentication API',
                'description' => 'Create REST API endpoints for user registration, login, and password reset functionality using JWT tokens.',
                'due_date' => '2025-05-21 00:00:00',
                'status' => 'completed',
                'priority' => 'high',
                'created_at' => '2025-05-14 02:57:47',
                'updated_at' => '2025-05-15 03:06:12',
                'progress' => '100'
            ],
            [
                'id' => '2',
                'user_id' => '4',
                'title' => 'Design Database Schema',
                'description' => 'Create ERD and implement database schema for new notification system including tables for user preferences and message templates.',
                'due_date' => '2025-05-20 00:00:00',
                'status' => 'pending',
                'priority' => 'medium',
                'created_at' => '2025-05-14 02:58:35',
                'updated_at' => '2025-05-19 09:23:08',
                'progress' => '0'
            ],
            [
                'id' => '3',
                'user_id' => '10',
                'title' => 'Develop Mobile App Frontend',
                'description' => 'Create UI components for task management screens following the provided design mockups in Figma.',
                'due_date' => '2025-05-19 00:00:00',
                'status' => 'pending',
                'priority' => 'medium',
                'created_at' => '2025-05-14 02:59:13',
                'updated_at' => '2025-05-14 02:59:13',
                'progress' => '0'
            ],
            [
                'id' => '4',
                'user_id' => '11',
                'title' => 'Implement Push Notification Service',
                'description' => 'Integrate Firebase Cloud Messaging for real-time notifications in the mobile app and implement notification delivery logic.',
                'due_date' => '2025-05-20 00:00:00',
                'status' => 'pending',
                'priority' => 'high',
                'created_at' => '2025-05-14 02:59:49',
                'updated_at' => '2025-05-14 02:59:49',
                'progress' => '0'
            ],
            [
                'id' => '5',
                'user_id' => '5',
                'title' => 'Server Backup Configuration',
                'description' => 'Set up automated nightly backups for the production database servers using the new backup solution. Configure retention policies according to compliance requirements.',
                'due_date' => '2025-05-17 00:00:00',
                'status' => 'completed',
                'priority' => 'high',
                'created_at' => '2025-05-14 03:01:42',
                'updated_at' => '2025-05-15 02:58:23',
                'progress' => '100'
            ],
            [
                'id' => '6',
                'user_id' => '5',
                'title' => 'Cloud Storage Migration Planning',
                'description' => 'Draft a detailed migration plan for moving on-premise file storage to cloud storage. Include cost analysis, timeline, and potential risks.',
                'due_date' => '2025-05-21 00:00:00',
                'status' => 'completed',
                'priority' => 'low',
                'created_at' => '2025-05-14 03:02:20',
                'updated_at' => '2025-05-15 03:01:40',
                'progress' => '100'
            ],
            [
                'id' => '7',
                'user_id' => '6',
                'title' => 'Monthly Sales Dashboard Optimization',
                'description' => 'Improve performance of the monthly sales dashboard and add regional comparison views',
                'due_date' => '2025-05-19 00:00:00',
                'status' => 'in-progress',
                'priority' => 'high',
                'created_at' => '2025-05-14 03:03:24',
                'updated_at' => '2025-05-19 04:22:38',
                'progress' => '40'
            ],
            [
                'id' => '8',
                'user_id' => '7',
                'title' => 'Data Backup and Recovery Testing',
                'description' => 'Test the backup and recovery procedures for our data warehouse and document the process',
                'due_date' => '2025-05-20 00:00:00',
                'status' => 'completed',
                'priority' => 'low',
                'created_at' => '2025-05-14 03:04:19',
                'updated_at' => '2025-05-15 03:04:24',
                'progress' => '100'
            ],
            [
                'id' => '9',
                'user_id' => '16',
                'title' => 'Create Unit Tests for API',
                'description' => 'Implement comprehensive unit tests for all REST API endpoints using PHPUnit. Ensure test coverage exceeds 85%.',
                'due_date' => '2025-05-15 00:00:00',
                'status' => 'pending',
                'priority' => 'high',
                'created_at' => '2025-05-15 02:51:07',
                'updated_at' => '2025-05-15 02:51:07',
                'progress' => '0'
            ],
            [
                'id' => '10',
                'user_id' => '15',
                'title' => 'Create 100 Users for UAT',
                'description' => 'Create 100 users consist of all roles with different name and email.',
                'due_date' => '2025-05-15 00:00:00',
                'status' => 'in-progress',
                'priority' => 'medium',
                'created_at' => '2025-05-15 03:10:45',
                'updated_at' => '2025-05-15 03:12:10',
                'progress' => '70'
            ],
            [
                'id' => '11',
                'user_id' => '6',
                'title' => 'Improve Database Design',
                'description' => '',
                'due_date' => '2025-05-20 00:00:00',
                'status' => 'pending',
                'priority' => 'medium',
                'created_at' => '2025-05-19 04:23:39',
                'updated_at' => '2025-05-19 04:23:39',
                'progress' => '0'
            ],
        ];

        // Using Query Builder
        $this->db->table('tasks')->insertBatch($data);
    }
}