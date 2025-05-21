<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NotificationsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id' => '1',
                'user_id' => '4',
                'task_id' => '1',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Implement User Authentication API. Please check your tasks for details.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-14 02:57:47',
                'updated_at' => '2025-05-15 03:06:07'
            ],
            [
                'id' => '2',
                'user_id' => '9',
                'task_id' => '2',
                'title' => 'Task Reassigned',
                'message' => 'Task \'Design Database Schema\' has been reassigned to Mohammad Hafizzudin.',
                'is_read' => '0',
                'type' => 'assignment',
                'created_at' => '2025-05-14 02:58:35',
                'updated_at' => '2025-05-19 09:09:24'
            ],
            [
                'id' => '3',
                'user_id' => '10',
                'task_id' => '3',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Develop Mobile App Frontend. Please check your tasks for details.',
                'is_read' => '0',
                'type' => 'assignment',
                'created_at' => '2025-05-14 02:59:13',
                'updated_at' => '2025-05-14 02:59:13'
            ],
            [
                'id' => '4',
                'user_id' => '11',
                'task_id' => '4',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Implement Push Notification Service. Please check your tasks for details.',
                'is_read' => '0',
                'type' => 'assignment',
                'created_at' => '2025-05-14 02:59:49',
                'updated_at' => '2025-05-14 02:59:49'
            ],
            [
                'id' => '5',
                'user_id' => '5',
                'task_id' => '5',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Server Backup Configuration. Please check your tasks for details.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-14 03:01:42',
                'updated_at' => '2025-05-14 04:06:01'
            ],
            [
                'id' => '6',
                'user_id' => '5',
                'task_id' => '6',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Cloud Storage Migration Planning. Please check your tasks for details.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-14 03:02:20',
                'updated_at' => '2025-05-15 02:57:57'
            ],
            [
                'id' => '7',
                'user_id' => '6',
                'task_id' => '7',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Monthly Sales Dashboard Optimization. Please check your tasks for details.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-14 03:03:24',
                'updated_at' => '2025-05-15 03:03:26'
            ],
            [
                'id' => '8',
                'user_id' => '7',
                'task_id' => '8',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Data Backup and Recovery Testing. Please check your tasks for details.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-14 03:04:19',
                'updated_at' => '2025-05-15 03:04:16'
            ],
            [
                'id' => '9',
                'user_id' => '5',
                'task_id' => '5',
                'title' => 'Task Status Updated',
                'message' => 'Status for task \'Server Backup Configuration\' has been updated to \'in-progress\'.',
                'is_read' => '1',
                'type' => 'status',
                'created_at' => '2025-05-14 04:06:07',
                'updated_at' => '2025-05-15 02:58:15'
            ],
            [
                'id' => '10',
                'user_id' => '16',
                'task_id' => '9',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Create Unit Tests for API. Please check your tasks for details.',
                'is_read' => '0',
                'type' => 'assignment',
                'created_at' => '2025-05-15 02:51:08',
                'updated_at' => '2025-05-15 02:51:08'
            ],
            [
                'id' => '11',
                'user_id' => '5',
                'task_id' => '5',
                'title' => 'Task Status Updated',
                'message' => 'Status for task \'Server Backup Configuration\' has been updated to \'completed\'.',
                'is_read' => '1',
                'type' => 'status',
                'created_at' => '2025-05-15 02:58:23',
                'updated_at' => '2025-05-15 02:58:34'
            ],
            [
                'id' => '12',
                'user_id' => '5',
                'task_id' => '6',
                'title' => 'Task Status Updated',
                'message' => 'Status for task \'Cloud Storage Migration Planning\' has been updated to \'completed\'.',
                'is_read' => '1',
                'type' => 'status',
                'created_at' => '2025-05-15 03:01:40',
                'updated_at' => '2025-05-15 03:01:50'
            ],
            [
                'id' => '13',
                'user_id' => '6',
                'task_id' => '7',
                'title' => 'Task Status Updated',
                'message' => 'Status for task \'Monthly Sales Dashboard Optimization\' has been updated to \'in-progress\'.',
                'is_read' => '1',
                'type' => 'status',
                'created_at' => '2025-05-15 03:03:36',
                'updated_at' => '2025-05-15 03:03:41'
            ],
            [
                'id' => '14',
                'user_id' => '7',
                'task_id' => '8',
                'title' => 'Task Status Updated',
                'message' => 'Status for task \'Data Backup and Recovery Testing\' has been updated to \'completed\'.',
                'is_read' => '1',
                'type' => 'status',
                'created_at' => '2025-05-15 03:04:24',
                'updated_at' => '2025-05-15 03:04:30'
            ],
            [
                'id' => '15',
                'user_id' => '4',
                'task_id' => '1',
                'title' => 'Task Status Updated',
                'message' => 'Status for task \'Implement User Authentication API\' has been updated to \'completed\'.',
                'is_read' => '1',
                'type' => 'status',
                'created_at' => '2025-05-15 03:06:12',
                'updated_at' => '2025-05-15 03:06:20'
            ],
            [
                'id' => '16',
                'user_id' => '15',
                'task_id' => '10',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Create 100 Users for UAT. Please check your tasks for details.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-15 03:10:45',
                'updated_at' => '2025-05-15 03:10:56'
            ],
            [
                'id' => '17',
                'user_id' => '15',
                'task_id' => '10',
                'title' => 'Task Status Updated',
                'message' => 'Status for task \'Create 100 Users for UAT\' has been updated to \'in-progress\'.',
                'is_read' => '1',
                'type' => 'status',
                'created_at' => '2025-05-15 03:12:10',
                'updated_at' => '2025-05-15 03:12:17'
            ],
            [
                'id' => '20',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 60% to 70%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 02:46:37',
                'updated_at' => '2025-05-19 02:46:44'
            ],
            [
                'id' => '21',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 70% to 80%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 02:47:18',
                'updated_at' => '2025-05-19 02:50:35'
            ],
            [
                'id' => '22',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 80% to 90%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 02:51:10',
                'updated_at' => '2025-05-19 02:51:19'
            ],
            [
                'id' => '23',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 90% to 95%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 02:58:25',
                'updated_at' => '2025-05-19 02:58:32'
            ],
            [
                'id' => '24',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 95% to 80%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 03:41:48',
                'updated_at' => '2025-05-19 03:41:56'
            ],
            [
                'id' => '25',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 80% to 90%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 03:59:23',
                'updated_at' => '2025-05-19 03:59:46'
            ],
            [
                'id' => '26',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 90% to 95%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 03:59:59',
                'updated_at' => '2025-05-19 04:00:13'
            ],
            [
                'id' => '27',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 95% to 10%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 04:09:45',
                'updated_at' => '2025-05-19 04:09:56'
            ],
            [
                'id' => '28',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 10% to 20%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 04:12:44',
                'updated_at' => '2025-05-19 04:12:56'
            ],
            [
                'id' => '29',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 20% to 30%.',
                'is_read' => '1',
                'type' => 'progress',
                'created_at' => '2025-05-19 04:13:10',
                'updated_at' => '2025-05-19 04:13:17'
            ],
            [
                'id' => '30',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Progress Updated',
                'message' => 'Progress for task \'Monthly Sales Dashboard Optimization\' has been updated from 30% to 40%.',
                'is_read' => '0',
                'type' => 'progress',
                'created_at' => '2025-05-19 04:13:27',
                'updated_at' => '2025-05-19 04:13:27'
            ],
            [
                'id' => '31',
                'user_id' => '1',
                'task_id' => '7',
                'title' => 'Task Priority Updated',
                'message' => 'Priority for task \'Monthly Sales Dashboard Optimization\' has been changed to \'high\'.',
                'is_read' => '0',
                'type' => 'priority',
                'created_at' => '2025-05-19 04:22:38',
                'updated_at' => '2025-05-19 04:22:38'
            ],
            [
                'id' => '32',
                'user_id' => '6',
                'task_id' => '11',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Improve Database Design. Please check your tasks for details.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-19 04:23:39',
                'updated_at' => '2025-05-19 04:23:47'
            ],
            [
                'id' => '33',
                'user_id' => '4',
                'task_id' => '2',
                'title' => 'Task Reassigned',
                'message' => 'Task \'Design Database Schema\' has been reassigned to Rohani.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-19 09:09:24',
                'updated_at' => '2025-05-19 09:10:05'
            ],
            [
                'id' => '34',
                'user_id' => '9',
                'task_id' => '2',
                'title' => 'Task Reassigned',
                'message' => 'Task \'Design Database Schema\' has been reassigned to Mohammad Hafizzudin.',
                'is_read' => '0',
                'type' => 'assignment',
                'created_at' => '2025-05-19 09:10:01',
                'updated_at' => '2025-05-19 09:10:17'
            ],
            [
                'id' => '35',
                'user_id' => '4',
                'task_id' => '2',
                'title' => 'Task Reassigned',
                'message' => 'Task \'Design Database Schema\' has been reassigned to Rohani.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-19 09:10:17',
                'updated_at' => '2025-05-19 09:14:33'
            ],
            [
                'id' => '36',
                'user_id' => '9',
                'task_id' => '2',
                'title' => 'Task Reassigned',
                'message' => 'Task \'Design Database Schema\' has been reassigned to Mohammad Hafizzudin.',
                'is_read' => '0',
                'type' => 'assignment',
                'created_at' => '2025-05-19 09:14:16',
                'updated_at' => '2025-05-19 09:14:46'
            ],
            [
                'id' => '37',
                'user_id' => '4',
                'task_id' => '2',
                'title' => 'Task Reassigned',
                'message' => 'Task \'Design Database Schema\' has been reassigned to Rohani.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-19 09:14:46',
                'updated_at' => '2025-05-19 09:19:05'
            ],
            [
                'id' => '38',
                'user_id' => '9',
                'task_id' => '2',
                'title' => 'Task Reassigned',
                'message' => 'Task \'Design Database Schema\' has been reassigned to Mohammad Hafizzudin.',
                'is_read' => '0',
                'type' => 'assignment',
                'created_at' => '2025-05-19 09:19:01',
                'updated_at' => '2025-05-19 09:19:21'
            ],
            [
                'id' => '39',
                'user_id' => '4',
                'task_id' => '2',
                'title' => 'Task Reassigned',
                'message' => 'Task \'Design Database Schema\' has been reassigned to Rohani.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-19 09:19:21',
                'updated_at' => '2025-05-19 09:22:56'
            ],
            [
                'id' => '40',
                'user_id' => '9',
                'task_id' => '2',
                'title' => 'Task Reassigned',
                'message' => 'Task \'Design Database Schema\' has been reassigned to Mohammad Hafizzudin.',
                'is_read' => '0',
                'type' => 'assignment',
                'created_at' => '2025-05-19 09:19:57',
                'updated_at' => '2025-05-19 09:23:08'
            ],
            [
                'id' => '41',
                'user_id' => '4',
                'task_id' => '2',
                'title' => 'Task Assigned',
                'message' => 'You have been assigned to task: Design Database Schema. Please check your tasks for details.',
                'is_read' => '1',
                'type' => 'assignment',
                'created_at' => '2025-05-19 09:23:08',
                'updated_at' => '2025-05-19 09:23:17'
            ],
        ];

        // Using Query Builder
        $this->db->table('notifications')->insertBatch($data);
    }
}