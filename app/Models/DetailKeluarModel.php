<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailKeluarModel extends Model
{
    protected $table            = 'detail_keluar';
    protected $primaryKey       = 'id_detail_keluar';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_keluar', 'id_barang', 'quantity', 'harga_satuan', 'subtotal',
    ];
}
