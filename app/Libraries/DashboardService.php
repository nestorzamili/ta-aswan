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
            'SELECT COALESCE(SUM(stok),0) AS t FROM barang WHERE deleted_at IS NULL'
        )->getRow()->t;
        
        $kritis = (int) $db->query(
            "SELECT COUNT(*) AS t FROM barang
             WHERE deleted_at IS NULL AND status_stok IN ('rendah', 'habis')"
        )->getRow()->t;
        
        $bulan = date('Y-m');
        $trxMasuk = (int) $db->table('barang_masuk')->like('tanggal_masuk', $bulan, 'after')->countAllResults();
        $trxKeluar = (int) $db->table('barang_keluar')->like('tanggal_keluar', $bulan, 'after')->countAllResults();

        return [
            'sparepart'   => $sp,
            'aksesoris'   => $ak,
            'unit_stok'   => $unit,
            'trx_bulan'   => $trxMasuk + $trxKeluar,
            'stok_kritis' => $kritis,
        ];
    }

    public function getStatusStok(): array
    {
        $db = db_connect();
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
        $db    = db_connect();
        $start = date('Y-m-d', strtotime('-13 days'));
        $end   = date('Y-m-d');

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
        ", [$start, $end, $start, $end])->getResultArray();

        $indexed = array_column($query, null, 'tanggal');
        $out     = [];

        for ($i = 13; $i >= 0; $i--) {
            $d     = date('Y-m-d', strtotime("-{$i} days"));
            $out[] = [
                'tanggal' => $d,
                'masuk'   => isset($indexed[$d]) ? (int) $indexed[$d]['masuk'] : 0,
                'keluar'  => isset($indexed[$d]) ? (int) $indexed[$d]['keluar'] : 0,
            ];
        }

        return $out;
    }
}
