<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table            = 'supplier';
    protected $primaryKey       = 'id_supplier';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'nama_supplier', 'alamat', 'telepon', 'email', 'deleted_at',
    ];
}
