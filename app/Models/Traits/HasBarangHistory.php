<?php

namespace App\Models\Traits;

trait HasBarangHistory
{
    public function hasTransactionHistory(int $id): bool
    {
        $db  = $this->db;
        $in  = $db->table('detail_masuk')->where('id_barang', $id)->countAllResults();
        $out = $db->table('detail_keluar')->where('id_barang', $id)->countAllResults();

        return ($in + $out) > 0;
    }
}
