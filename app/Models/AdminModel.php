<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table            = 'admin';
    protected $primaryKey       = 'id_admin';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'username', 'password', 'nama', 'email', 'nomor_telepon', 'level', 'status', 'deleted_at',
    ];
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password']) && ! str_starts_with($data['data']['password'], '$2y$')) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }

        return $data;
    }

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }
}
