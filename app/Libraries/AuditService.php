<?php

namespace App\Libraries;

use App\Models\ActivityLogModel;
use Throwable;

class AuditService
{
    private ActivityLogModel $model;

    public function __construct(?ActivityLogModel $model = null)
    {
        $this->model = $model ?? new ActivityLogModel();
    }

    public function log(string $action, ?string $entity = null, ?int $entityId = null, ?string $description = null): void
    {
        try {
            $this->model->insert([
                'id_admin'    => session('id_admin') !== null ? (int) session('id_admin') : null,
                'nama_admin'  => session('nama') !== null ? (string) session('nama') : null,
                'action'      => $action,
                'entity'      => $entity,
                'entity_id'   => $entityId,
                'description' => $description,
                'ip_address'  => service('request')->getIPAddress(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            log_message('error', 'AuditService gagal mencatat: ' . $e->getMessage());
        }
    }
}
