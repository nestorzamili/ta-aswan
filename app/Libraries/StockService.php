<?php

namespace App\Libraries;

use App\Exceptions\BusinessException;
use Config\Database;

class StockService
{
    private const MSG_QTY_POSITIVE = 'Qty stok harus lebih dari 0.';
    private const MSG_NOT_FOUND    = 'Barang tidak ditemukan.';

    public function statusFromQty(int $qty): string
    {
        if ($qty <= 0) {
            return 'habis';
        }
        if ($qty < 3) {
            return 'rendah';
        }

        return 'aman';
    }

    public function increase(int $idBarang, int $qty): void
    {
        if ($qty <= 0) {
            throw new BusinessException(self::MSG_QTY_POSITIVE);
        }
        $this->adjustAtomic($idBarang, $qty, false);
    }

    public function decrease(int $idBarang, int $qty): void
    {
        if ($qty <= 0) {
            throw new BusinessException(self::MSG_QTY_POSITIVE);
        }
        $this->adjustAtomic($idBarang, -$qty, true);
    }

    /**
     * Early UX check (non-atomic). Final authority is decrease().
     */
    public function assertAvailable(int $idBarang, int $qty): void
    {
        if ($qty <= 0) {
            throw new BusinessException(self::MSG_QTY_POSITIVE);
        }

        $db  = Database::connect();
        $row = $db->table('barang')
            ->select('stok, nama_barang')
            ->where('id_barang', $idBarang)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (! $row) {
            throw new BusinessException(self::MSG_NOT_FOUND);
        }

        if ((int) $row['stok'] < $qty) {
            $nama = $row['nama_barang'] ?? '-';

            throw new BusinessException("Stok tidak cukup untuk: {$nama}");
        }
    }

    protected function adjustAtomic(int $idBarang, int $delta, bool $failIfInsufficient): void
    {
        $db      = Database::connect();
        $table   = $db->prefixTable('barang');
        $pk      = 'id_barang';
        $namaCol = 'nama_barang';

        // MySQL evaluates SET left-to-right: after assigning `stok`, CASE must use
        // the *new* stok value (do not add $delta again inside CASE).
        if ($failIfInsufficient && $delta < 0) {
            $need = abs($delta);
            $sql  = "UPDATE `{$table}` SET
                `stok` = `stok` + ?,
                `status_stok` = CASE
                    WHEN `stok` <= 0 THEN 'habis'
                    WHEN `stok` < 3 THEN 'rendah'
                    ELSE 'aman'
                END
                WHERE `{$pk}` = ? AND `stok` >= ? AND `deleted_at` IS NULL";
            $db->query($sql, [$delta, $idBarang, $need]);
            if ($db->affectedRows() === 0) {
                $row = $db->table('barang')
                    ->select($namaCol)
                    ->where($pk, $idBarang)
                    ->where('deleted_at', null)
                    ->get()
                    ->getRowArray();
                if (! $row) {
                    throw new BusinessException(self::MSG_NOT_FOUND);
                }
                $nama = $row[$namaCol] ?? '-';

                throw new BusinessException("Stok tidak cukup untuk: {$nama}");
            }

            return;
        }

        $sql = "UPDATE `{$table}` SET
            `stok` = GREATEST(0, `stok` + ?),
            `status_stok` = CASE
                WHEN `stok` <= 0 THEN 'habis'
                WHEN `stok` < 3 THEN 'rendah'
                ELSE 'aman'
            END
            WHERE `{$pk}` = ? AND `deleted_at` IS NULL";
        $db->query($sql, [$delta, $idBarang]);
        if ($db->affectedRows() === 0) {
            throw new BusinessException(self::MSG_NOT_FOUND);
        }
    }

    public function setStatusForRow(array $data): array
    {
        $stok                = (int) ($data['stok'] ?? 0);
        $data['status_stok'] = $this->statusFromQty($stok);

        return $data;
    }
}
