<?php

namespace App\Libraries;

use App\Exceptions\BusinessException;
use Config\Database;

class NumberingService
{
    /**
     * Generate next document number with MySQL advisory lock.
     *
     * @param string      $prefix     e.g. SP, AK, FM, TK
     * @param string      $table      DB table
     * @param string      $column     number column
     * @param string|null $tipeBarang optional filter for unified barang table
     */
    public function next(string $prefix, string $table, string $column, ?string $tipeBarang = null): string
    {
        $year     = date('Y');
        $lockName = 'num_' . $table . '_' . $prefix . '_' . $year;
        $db       = Database::connect();

        $got = $db->query('SELECT GET_LOCK(?, 10) AS locked', [$lockName])->getRowArray();
        if ((int) ($got['locked'] ?? 0) !== 1) {
            throw new BusinessException('Gagal mengambil nomor dokumen. Coba lagi.');
        }

        try {
            // Query Builder table() does not apply soft-delete scopes (Model-only),
            // so soft-deleted codes still participate in sequence uniqueness.
            $builder = $db->table($table)
                ->select($column)
                ->like($column, "{$prefix}-{$year}-", 'after');

            if ($tipeBarang !== null && $table === 'barang') {
                $builder->where('tipe_barang', $tipeBarang);
            }

            $row = $builder->orderBy($column, 'DESC')->get(1)->getRowArray();

            $seq = 1;
            if (! empty($row[$column])) {
                $parts = explode('-', (string) $row[$column]);
                $seq   = (int) end($parts) + 1;
            }

            return sprintf('%s-%s-%04d', $prefix, $year, $seq);
        } finally {
            $db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    public function nextSparepart(): string
    {
        return $this->next('SP', 'barang', 'kode_barang', 'sparepart');
    }

    public function nextAksesoris(): string
    {
        return $this->next('AK', 'barang', 'kode_barang', 'aksesoris');
    }

    public function nextFakturMasuk(): string
    {
        return $this->next('FM', 'barang_masuk', 'no_faktur');
    }

    public function nextTransaksiKeluar(): string
    {
        return $this->next('TK', 'barang_keluar', 'no_transaksi');
    }
}
