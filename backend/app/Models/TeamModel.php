<?php

namespace App\Models;

use CodeIgniter\Model;

class TeamModel extends Model
{
    protected $table            = 'teams';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = null;

    // Validation
    protected $validationRules      = [
        'name' => 'required|max_length[255]',
        'description' => 'permit_empty'
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
     * Get all members of a team
     *
     * @param int $teamId
     * @return array
     */
    public function getTeamMembers($teamId)
    {
        $db = \Config\Database::connect();
        
        // Option 1: If using direct team_id in users table
        $builder = $db->table('users');
        $builder->select('users.id, users.name, users.email, users.role');
        $builder->where('users.team_id', $teamId);
        
        return $builder->get()->getResultArray();
    }

    /**
     * Alternative method if using the team_members junction table
     *
     * @param int $teamId
     * @return array
     */
    public function getTeamMembersDetailed($teamId)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('team_members');
        $builder->select('users.id, users.name, users.email, users.role, team_members.role as team_role');
        $builder->join('users', 'users.id = team_members.user_id');
        $builder->where('team_members.team_id', $teamId);
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get all teams with member count
     *
     * @return array
     */
    public function getTeamsWithMemberCount()
    {
        $db = \Config\Database::connect();
        
        // Option 1: If using direct team_id in users table
        $builder = $db->table('teams');
        $builder->select('teams.*, COUNT(users.id) as member_count');
        $builder->join('users', 'users.team_id = teams.id', 'left');
        $builder->groupBy('teams.id');
        
        return $builder->get()->getResultArray();
    }
}
