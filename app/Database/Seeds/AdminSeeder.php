<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Get password from environment, default to 'secret' if not set
        $rawPassword = env('admin.defaultPassword', 'secret');
        $hash        = password_hash($rawPassword, PASSWORD_DEFAULT);
        $now         = date('Y-m-d H:i:s');

        $data = [
            [
                'username'      => 'admin',
                'password'      => $hash,
                'nama'          => 'Admin',
                'email'         => 'admin@androidservice.local',
                'nomor_telepon' => '081200000001',
                'level'         => 'admin',
                'status'        => 'aktif',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];
        $this->db->table('admin')->insertBatch($data);
    }
}
