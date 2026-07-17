<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailMasukModel extends Model
{
    protected $table            = 'detail_masuk';
    protected $primaryKey       = 'id_detail_masuk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_masuk', 'id_barang', 'quantity', 'harga_satuan', 'subtotal',
    ];
}
