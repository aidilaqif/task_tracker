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
