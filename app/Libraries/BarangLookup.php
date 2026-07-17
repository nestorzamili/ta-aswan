<?php

namespace App\Libraries;

use App\Exceptions\BusinessException;
use App\Models\BarangModel;

class BarangLookup
{
    public function enrichDetails(array $details): array
    {
        $barangIds = [];

        foreach ($details as $d) {
            $barangIds[] = (int) $d['id_barang'];
        }

        $barangData = [];
        if ($barangIds !== []) {
            $rows = (new BarangModel())->whereIn('id_barang', array_unique($barangIds))->findAll();

            foreach ($rows as $r) {
                $barangData[$r['id_barang']] = $r;
            }
        }

        foreach ($details as &$d) {
            $id               = (int) $d['id_barang'];
            $row              = $barangData[$id] ?? null;
            $d['tipe_barang'] = $row['tipe_barang'] ?? '-';
            $d['nama_barang'] = $row['nama_barang'] ?? '-';
            $d['kode']        = $row['kode_barang'] ?? '-';
        }

        return $details;
    }

    /**
     * @throws BusinessException when barang missing or soft-deleted
     */
    public function meta(int $id): array
    {
        $row = (new BarangModel())->find($id);
        if (! $row) {
            throw new BusinessException('Barang tidak ditemukan atau sudah dihapus.');
        }

        return [
            'tipe'       => $row['tipe_barang'],
            'nama'       => $row['nama_barang'],
            'kode'       => $row['kode_barang'],
            'harga_jual' => (float) $row['harga_jual'],
            'harga_beli' => (float) $row['harga_beli'],
            'stok'       => (int) $row['stok'],
        ];
    }
}
