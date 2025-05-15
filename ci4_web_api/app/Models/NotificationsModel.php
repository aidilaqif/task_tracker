<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationsModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'task_id', 'title', 'message', 'is_read', 'type'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'is_read' => 'boolean'
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = null;

    // Validation
    protected $validationRules      = [
        'user_id' => 'required|integer',
        'task_id' => 'permit_empty|integer',
        'title' => 'required|max_length[255]',
        'message' => 'required',
        'is_read' => 'permit_empty|in_list[0,1]',
        'type' => 'permit_empty|max_length[50]'
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
     * Get notifications with filters and pagination
     *
     * @param array $filters Associative array of filters
     * @param int $page Current page number
     * @param int $limit Items per page
     * @return array
     */
    public function getNotificationsWithFilters($filters = [], $page = 1, $limit = 10)
    {
        $builder = $this->builder();

        // Apply filters
        foreach ($filters as $field => $value) {
            if ($field === 'user_id' || $field === 'task_id') {
                $builder->where($field, $value);
            } elseif ($field === 'is_read') {
                $builder->where($field, $value);
            } elseif ($field === 'type') {
                $builder->where($field, $value);
            }
        }

        // Order by most recent first
        $builder->orderBy('created_at', 'DESC');

        // Apply pagination
        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);

        // Execute query
        return $builder->get()->getResultArray();
    }

    /**
     * Count notifications with filters
     *
     * @param array $filters Associative array of filters
     * @return int
     */
    public function countNotificationsWithFilters($filters = [])
    {
        $builder = $this->builder();

        // Apply filters
        foreach ($filters as $field => $value) {
            if ($field === 'user_id' || $field === 'task_id') {
                $builder->where($field, $value);
            } elseif ($field === 'is_read') {
                $builder->where($field, $value);
            } elseif ($field === 'type') {
                $builder->where($field, $value);
            }
        }

        // Count results
        return $builder->countAllResults();
    }

    /**
     * Get notifications by type
     *
     * @param string $type Notification type
     * @param int $page Current page number
     * @param int $limit Items per page
     * @return array
     */
    public function getNotificationsByType($type, $page = 1, $limit = 10)
    {
        $builder = $this->builder();

        // Filter by type
        $builder->where('type', $type);

        // Order by most recent first
        $builder->orderBy('created_at', 'DESC');

        // Apply pagination
        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);

        // Execute query
        return $builder->get()->getResultArray();
    }

    /**
     * Get unread count for admin
     *
     * @param int $userId User ID
     * @return int
     */
    public function getUnreadCountForAdmin($userId)
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }
}
