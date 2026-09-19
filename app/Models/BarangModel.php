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

    /**
     * @return list<string>
     */
    public function distinctKategori(string $tipe): array
    {
        return $this->distinctColumn('kategori', $tipe);
    }

    /**
     * @return list<string>
     */
    public function distinctMerk(string $tipe): array
    {
        return $this->distinctColumn('merk', $tipe);
    }

    /**
     * @return list<string>
     */
    private function distinctColumn(string $column, string $tipe): array
    {
        $rows = $this->db->table($this->table)
            ->select($column)
            ->distinct()
            ->where('tipe_barang', $tipe)
            ->where($column . ' !=', '')
            ->where('deleted_at', null)
            ->orderBy($column, 'ASC')
            ->get()
            ->getResultArray();

        return array_column($rows, $column);
    }
}
