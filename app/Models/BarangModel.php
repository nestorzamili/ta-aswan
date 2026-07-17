<?php

namespace App\Models;

use App\Models\Traits\HasBarangHistory;
use CodeIgniter\Model;

class BarangModel extends Model
{
    use HasBarangHistory;

    protected $table            = 'barang';
    protected $primaryKey       = 'id_barang';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'tipe_barang', 'kode_barang', 'kode_manual', 'nama_barang', 'kategori', 'merk',
        'satuan', 'harga_beli', 'harga_jual', 'stok', 'status_stok', 'deleted_at',
    ];
}
