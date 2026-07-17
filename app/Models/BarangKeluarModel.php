<?php

namespace App\Models;

use CodeIgniter\Model;

class BarangKeluarModel extends Model
{
    protected $table            = 'barang_keluar';
    protected $primaryKey       = 'id_keluar';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
    protected $allowedFields    = [
        'no_transaksi', 'tanggal_keluar', 'tujuan', 'total_item',
        'total_quantity', 'total_harga', 'id_admin', 'created_at',
    ];
}
