<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangMasukModel extends Model
{
    protected $table            = 'barang_masuk';
    protected $primaryKey       = 'id_masuk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
    protected $allowedFields    = [
        'no_faktur', 'tanggal_masuk', 'id_supplier', 'total_item',
        'total_quantity', 'total_harga', 'id_admin', 'created_at',
    ];
}
