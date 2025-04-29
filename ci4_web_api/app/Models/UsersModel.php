<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'email', 'password', 'role', 'team_id'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    // protected $dateFormat    = 'datetime';
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'name' => 'required|max_length[255]',
        'email' => 'required|max_length[255]',
        'password' => 'required|max_length[255]',
        'role' => 'required|max_length[255]',
        'team_id' => 'permit_empty|integer'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get users by team ID
     * 
     * @param int $teamId
     * @return array
     */
    public function getUsersByTeam($teamId)
    {
        return $this->where('team_id', $teamId)->findAll();
    }

    /**
     * Get users not in any team
     * 
     * @return array
     */
    public function getUsersWithoutTeam()
    {
        return $this->where('team_id is NULL')->findAll();
    }
    
    /**
     * Get available user roles
     *
     * @return array
     */
    public function getAvailableRoles()
    {
        // Define the available roles in the system
        return [
            'admin' => [
                'label' => 'Administrator',
                'description' => 'Full access to all system features'
            ],
            'user' => [
                'label' => 'Standard User',
                'description' => 'Basic access to assigned tasks and own profile'
            ]
            // Add more roles as needed
        ];
    }
    
    /**
     * Get all users with filtering
     *
     * @param array $filters Associative array of filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFilteredUsers($filters = [], $limit = 20, $offset = 0)
    {
        $builder = $this->builder();
        
        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $builder->groupStart()
                ->like('name', $filters['search'])
                ->orLike('email', $filters['search'])
                ->groupEnd();
        }
        
        // Apply role filter
        if (isset($filters['role']) && !empty($filters['role'])) {
            $builder->where('role', $filters['role']);
        }
        
        // Apply team filter
        if (isset($filters['team_id']) && !empty($filters['team_id'])) {
            $builder->where('team_id', $filters['team_id']);
        }
        
        // Apply limit and offset for pagination
        return $builder->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }
    
    /**
     * Count filtered users
     *
     * @param array $filters Associative array of filters
     * @return int
     */
    public function countFilteredUsers($filters = [])
    {
        $builder = $this->builder();
        
        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $builder->groupStart()
                ->like('name', $filters['search'])
                ->orLike('email', $filters['search'])
                ->groupEnd();
        }
        
        // Apply role filter
        if (isset($filters['role']) && !empty($filters['role'])) {
            $builder->where('role', $filters['role']);
        }
        
        // Apply team filter
        if (isset($filters['team_id']) && !empty($filters['team_id'])) {
            $builder->where('team_id', $filters['team_id']);
        }
        
        return $builder->countAllResults();
    }
    
    /**
     * Get user statistics
     *
     * @param int $userId
     * @return array
     */
    public function getUserStatistics($userId)
    {
        $db = \Config\Database::connect();
        
        // Get total assigned tasks
        $totalTasksQuery = $db->table('tasks')
            ->selectCount('id', 'total')
            ->where('user_id', $userId);
        $totalTasks = $totalTasksQuery->get()->getRow()->total ?? 0;
        
        // Get completed tasks
        $completedTasksQuery = $db->table('tasks')
            ->selectCount('id', 'completed')
            ->where('user_id', $userId)
            ->where('status', 'completed');
        $completedTasks = $completedTasksQuery->get()->getRow()->completed ?? 0;
        
        // Get tasks by priority
        $priorityTasksQuery = $db->table('tasks')
            ->select('priority, COUNT(*) as count')
            ->where('user_id', $userId)
            ->groupBy('priority');
        $priorityTasks = $priorityTasksQuery->get()->getResultArray();
        
        // Get tasks by status
        $statusTasksQuery = $db->table('tasks')
            ->select('status, COUNT(*) as count')
            ->where('user_id', $userId)
            ->groupBy('status');
        $statusTasks = $statusTasksQuery->get()->getResultArray();
        
        // Calculate completion rate
        $completionRate = ($totalTasks > 0) ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
        
        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'completion_rate' => $completionRate,
            'priority_breakdown' => $priorityTasks,
            'status_breakdown' => $statusTasks
        ];
    }
}