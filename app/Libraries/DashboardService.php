<?php

namespace App\Libraries;

class DashboardService
{
    public function getBarangMenipis(): array
    {
        $db = db_connect();
        return $db->query("
            SELECT tipe_barang AS tipe, id_barang AS id, kode_barang AS kode, nama_barang AS nama, stok, status_stok
            FROM barang
            WHERE status_stok IN ('rendah','habis')
              AND deleted_at IS NULL
            ORDER BY stok ASC LIMIT 10
        ")->getResultArray();
    }

    public function getAktivitasTerbaru(): array
    {
        $db = db_connect();
        return $db->query("
            (SELECT no_faktur AS nomor, tanggal_masuk AS tanggal, 'masuk' AS jenis, total_harga,
                    CONCAT('barang-masuk/', id_masuk) AS link
             FROM barang_masuk)
            UNION ALL
            (SELECT no_transaksi, tanggal_keluar, 'keluar', total_harga,
                    CONCAT('barang-keluar/', id_keluar) AS link
             FROM barang_keluar)
            ORDER BY tanggal DESC LIMIT 10
        ")->getResultArray();
    }

    public function getStatistikKpi(): array
    {
        $db = db_connect();

        $sp = (int) $db->table('barang')->where('tipe_barang', 'sparepart')->where('deleted_at', null)->countAllResults();
        $ak = (int) $db->table('barang')->where('tipe_barang', 'aksesoris')->where('deleted_at', null)->countAllResults();

        $unit = (int) $db->query(
            'SELECT COALESCE(SUM(stok),0) AS t FROM barang WHERE deleted_at IS NULL',
        )->getRow()->t;

        $nilaiPersediaan = (int) $db->query(
            'SELECT COALESCE(SUM(stok * harga_beli),0) AS t FROM barang WHERE deleted_at IS NULL',
        )->getRow()->t;

        $kritis = (int) $db->query(
            "SELECT COUNT(*) AS t FROM barang
             WHERE deleted_at IS NULL AND status_stok IN ('rendah', 'habis')",
        )->getRow()->t;

        $totalSupplier = (int) $db->table('supplier')->where('deleted_at', null)->countAllResults();

        [$from, $to] = $this->periodeDefault();

        $trxMasuk = (int) $db->table('barang_masuk')
            ->where('tanggal_masuk >=', $from)
            ->where('tanggal_masuk <=', $to)
            ->countAllResults();
        $trxKeluar = (int) $db->table('barang_keluar')
            ->where('tanggal_keluar >=', $from)
            ->where('tanggal_keluar <=', $to)
            ->countAllResults();

        return [
            'sparepart'        => $sp,
            'aksesoris'        => $ak,
            'unit_stok'        => $unit,
            'nilai_persediaan' => $nilaiPersediaan,
            'total_supplier'   => $totalSupplier,
            'trx_bulan'        => $trxMasuk + $trxKeluar,
            'stok_kritis'      => $kritis,
        ];
    }

    public function getStatusStok(): array
    {
        $db   = db_connect();
        $rows = $db->query('
            SELECT status_stok, COUNT(*) AS jml
            FROM barang
            WHERE deleted_at IS NULL
            GROUP BY status_stok
        ')->getResultArray();

        $out = ['aman' => 0, 'rendah' => 0, 'habis' => 0];
        foreach ($rows as $r) {
            $out[$r['status_stok']] = (int) $r['jml'];
        }

        return $out;
    }

    public function getStokKategori(): array
    {
        $db = db_connect();
        return $db->query("
            SELECT kategori, SUM(stok) AS total
            FROM barang
            WHERE deleted_at IS NULL
              AND kategori IS NOT NULL AND kategori != ''
            GROUP BY kategori ORDER BY total DESC LIMIT 8
        ")->getResultArray();
    }

    public function getTrenTransaksi(): array
    {
        $db = db_connect();

        [$from, $to] = $this->periodeDefault();

        $query = $db->query("
            SELECT tanggal,
                   SUM(CASE WHEN jenis = 'masuk' THEN 1 ELSE 0 END) AS masuk,
                   SUM(CASE WHEN jenis = 'keluar' THEN 1 ELSE 0 END) AS keluar
            FROM (
                SELECT tanggal_masuk AS tanggal, 'masuk' AS jenis FROM barang_masuk WHERE tanggal_masuk >= ? AND tanggal_masuk <= ?
                UNION ALL
                SELECT tanggal_keluar AS tanggal, 'keluar' AS jenis FROM barang_keluar WHERE tanggal_keluar >= ? AND tanggal_keluar <= ?
            ) t
            GROUP BY tanggal
        ", [$from, $to, $from, $to])->getResultArray();

        $indexed = array_column($query, null, 'tanggal');
        $out     = [];

        $cursor = strtotime($from);
        $last   = strtotime($to);
        while ($cursor <= $last) {
            $d     = date('Y-m-d', $cursor);
            $out[] = [
                'tanggal' => $d,
                'masuk'   => isset($indexed[$d]) ? (int) $indexed[$d]['masuk'] : 0,
                'keluar'  => isset($indexed[$d]) ? (int) $indexed[$d]['keluar'] : 0,
            ];
            $cursor = strtotime('+1 day', $cursor);
        }

        return $out;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function periodeDefault(): array
    {
        $to = date('Y-m-d');

        return [date('Y-m-d', strtotime('-13 days', strtotime($to))), $to];
    }
}
