<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TeamModel;
use App\Models\UsersModel;

class TeamController extends BaseController
{
    protected $teamModel;
    protected $usersModel;

    public function __construct()
    {
        $this->teamModel = new TeamModel();
        $this->usersModel = new UsersModel();
    }
    // Get a list of all teams
    public function getAllTeams()
    {
        $teams = $this->teamModel->findAll();

        return $this->respondWithJson(
            true,
            "Teams retrieved successfully",
            $teams
        );
    }
    // Get teams with member count
    public function getTeamsWithMemberCount()
    {
        $teams = $this->teamModel->getTeamsWithMemberCount();

        return $this->respondWithJson(
            true,
            "Teams with member count retrieved successfully",
            $teams
        );
    }
    // Get a specific team by ID
    public function getTeam($id)
    {
        $team = $this->teamModel->find($id);

        if(!$team) {
            return $this->respondWithJson(false, "Team not found", null, 404);
        }
        return $this->respondWithJson(true, "Team retrieved successfully", $team);
    }
    // Get all members of specific team
    public function getTeamMembers($teamId)
    {
        // Check if team exists
        $team = $this->teamModel->find($teamId);

        if(!$team) {
            return $this->respondWithJson(false, "Team not found", null, 404);
        }

        // Get team members
        $members = $this->teamModel->getTeamMembers($teamId);

        // Prepare response with both team info and members
        $response = [
            'team' => $team,
            'members' => $members
        ];

        return $this->respondWithJson(
            true,
            "Team members retrieved successfully",
            $response
        );
    }
    // Create new team
    public function createTeam()
    {
        $input = $this->request->getJSON();

        if(!isset($input->name)){
            return $this->respondWithJson(false, "Team name is required", null, 400);
        }

        $data = [
            'name' => $input->name,
            'description' => $input->description ?? null
        ];

        try {
            if ($this->teamModel->insert($data)) {
                $teamId = $this->teamModel->getInsertID();
                $team = $this->teamModel->find($teamId);

                return $this->respondWithJson(true, "Team created successfully", $team);
            } else {
                $errors = $this->teamModel->errors();
                return $this->respondWithJson(false, "Failed to create team", $errors, 400);
            }
        } catch (\Exception $e){
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    // Add a user to a team
    public function addUserToTeam()
    {
        $input = $this->request->getJSON();

        if (!isset($input->user_id) || !isset($input->team_id)) {
            return $this->respondWithJson(false, "User ID and Team ID are required", null, 400);
        }

        // Check if team exists
        $team = $this->teamModel->find($input->team_id);
        if (!$team) {
            return $this->respondWithJson(false, "Team not found", null. 404);
        }

        // Check if user exists
        $user = $this->usersModel->find($input->user_id);
        if(!$user) {
            return $this->respondWithJson(false, "User not found", null, 404);
        }

        // Update user's team_id
        try {
            if ($this->usersModel->update($input->user_id, ['team_id' => $input->team_id])){
                return $this->respondWithJson(true, "User added to team successfully");
            } else {
                $errors = $this->usersModel->errors();
                return $this->respondWithJson(false, "Failed to add user to team", $errors, 400);
            }
        } catch (\Exception $e){
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    // Remove a user from a team
    public function removeUserFromTeam($userId)
    {
        // Check if user exists
        $user = $this->usersModel->find($userId);
        if(!$user){
            return $this->respondWithJson(false, "User not found", null, 404);
        }

        // Update user's team_id to null
        try {
            if ($this->usersModel->update($userId, ['team_id' => null])) {
                return $this->respondWithJson(true, "User removed from team successfully");
            } else {
                $errors = $this->usersModel->errors();
                return $this->respondWithJson(false, "Failed to remove user from tema", $errors);
            }
        } catch (\Exception $e) {
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }

    // Get team workload and performance metrics
    public function getTeamPerformanceMetrics($teamId)
    {
        // Check if team exists
        $team = $this->teamModel->find($teamId);
        if (!$team) {
            return $this->respondWithJson(false, "Team not found", null, 404);
        }

        try {
            $metrics = [];
            $db = db_connect();
            
            // 1. Get team members with their task counts and workload
            $builder = $db->table('users');
            $builder->select('users.id, users.name, COUNT(tasks.id) as total_tasks,
                            SUM(CASE WHEN tasks.status = "completed" THEN 1 ELSE 0 END) as completed_tasks,
                            AVG(tasks.progress) as avg_progress');
            $builder->join('tasks', 'users.id = tasks.user_id', 'left');
            $builder->where('users.team_id', $teamId);
            $builder->groupBy('users.id, users.name');

            $memberWorkload = $builder->get()->getResultArray();

            // Calculate completion rates and add to the result
            foreach ($memberWorkload as &$member) {
                $member['completion_rate'] = $member['total_tasks'] > 0 ?
                    round(($member['completed_tasks'] / $member['total_tasks']) * 100, 2) : 0;
                $member['avg_progress'] = round($member['avg_progress'] ?? 0, 2);
            }

            $metrics['member_workload'] = $memberWorkload;

            // 2. Get task priority distribution across the team
            $builder = $db->table('tasks');
            $builder->select('tasks.priority, COUNT(*) as count');
            $builder->join('users','users.id = tasks.user_id');
            $builder->where('users.team_id', $teamId);
            $builder->groupBy('tasks.priority');

            $priorityDistribution = $builder->get()->getResultArray();
            $metrics['priority_distribution'] = $priorityDistribution;

            // 3. Get task status distribution across the team
            $builder = $db->table('tasks');
            $builder->select('tasks.status, COUNT(*) as count');
            $builder->join('users', 'users.id = tasks.user_id');
            $builder->where('users.team_id', $teamId);
            $builder->groupBy('tasks.status');

            $statusDistribution = $builder->get()->getResultArray();
            $metrics['status_distribution'] = $statusDistribution;

            // 4. Get overdue tasks count
            $builder = $db->table('tasks');
            $builder->select('COUNT(*) as overdue_count');
            $builder->join('users', 'users.id = tasks.user_id');
            $builder->where('users.team_id', $teamId);
            $builder->where('tasks.due_date <', date('Y-m-d'));
            $builder->where('tasks.status !=', 'completed');

            $overdueResult = $builder->get()->getRowArray();
            $metrics['overdue_tasks'] = (int)$overdueResult['overdue_count'];

            // 5. Calculate team's overall completion rate
            $builder = $db->table('tasks');
            $builder->select('COUNT(*) as total, SUM(CASE WHEN tasks.status = "completed" THEN 1 ELSE 0 END) as completed');
            $builder->join('users', 'users.id = tasks.user_id');
            $builder->where('users.team_id', $teamId);

            $overall = $builder->get()->getRowArray();
            $metrics['team_completion_rate'] = $overall['total'] > 0 ?
                round(($overall['completed'] / $overall['total']) * 100, 2) : 0;

            // 6. Get average time to complete tasks (in days)
            $builder = $db->table('tasks');
            $builder->select('AVG(DATEDIFF(tasks.updated_at, tasks.created_at)) as avg_completion_time');
            $builder->join('users', 'users.id = tasks.user_id');
            $builder->where('users.team_id', $teamId);
            $builder->where('tasks.status', 'completed');

            $timeResult = $builder->get()->getRowArray();
            $metrics['avg_completion_time'] = round($timeResult['avg_completion_time'] ?? 0, 1);

            return $this->respondWithJson(true, "Team performance metrics retrieved successfully", $metrics);
        } catch (\Exception $e){
            return $this->respondWithJson(false, "Internal Server Error", $e->getMessage(), 500);
        }
    }
    // Standard JSON response method
    private function respondWithJson($status, $msg, $data = null, $statusCode = 200)
    {
        $response = [
            "status" => $status,
            "msg" => $msg
        ];

        if ($data !== null){
            $response['data'] = $data;
        }

        return $this->response->setJSON($response)->setStatusCode($statusCode);
    }
}
