<?php

namespace App\Controllers;

class LaporanController extends BaseController
{
    public function index()
    {
        return view('laporan/index', ['title' => 'Laporan']);
    }

    public function pdf()
    {
        $jenis   = (string) ($this->request->getGet('jenis') ?? $this->request->getPost('jenis'));
        $allowed = ['stok', 'masuk', 'keluar'];
        if (! in_array($jenis, $allowed, true)) {
            return redirect()->to('/laporan')->with('error', 'Jenis laporan tidak valid.');
        }

        $rawAwal  = (string) ($this->request->getGet('tanggal_awal') ?? $this->request->getPost('tanggal_awal'));
        $rawAkhir = (string) ($this->request->getGet('tanggal_akhir') ?? $this->request->getPost('tanggal_akhir'));
        $awal     = $this->parseDateFilter($rawAwal) ?: date('Y-m-01');
        $akhir    = $this->parseDateFilter($rawAkhir) ?: date('Y-m-d');
        if (strtotime($awal) > strtotime($akhir)) {
            [$awal, $akhir] = [$akhir, $awal];
        }
        $db = db_connect();

        if ($jenis === 'stok') {
            $items = $db->query('
                SELECT tipe_barang AS tipe, kode_barang AS kode, nama_barang AS nama, stok, status_stok
                FROM barang
                WHERE deleted_at IS NULL
                ORDER BY tipe, nama
            ')->getResultArray();
            $view     = 'pdf/laporan_stok';
            $filename = 'Laporan-Stok-' . date('Ymd') . '.pdf';
        } elseif ($jenis === 'masuk') {
            $items = $db->table('barang_masuk bm')
                ->select('bm.*, s.nama_supplier')
                ->join('supplier s', 's.id_supplier = bm.id_supplier')
                ->where('bm.tanggal_masuk >=', $awal)
                ->where('bm.tanggal_masuk <=', $akhir)
                ->orderBy('bm.tanggal_masuk')
                ->get()->getResultArray();
            $view     = 'pdf/laporan_masuk';
            $filename = 'Laporan-Masuk-' . $awal . '_' . $akhir . '.pdf';
        } else {
            $items = $db->table('barang_keluar')
                ->where('tanggal_keluar >=', $awal)
                ->where('tanggal_keluar <=', $akhir)
                ->orderBy('tanggal_keluar')
                ->get()->getResultArray();
            $view     = 'pdf/laporan_keluar';
            $filename = 'Laporan-Keluar-' . $awal . '_' . $akhir . '.pdf';
        }

        return $this->pdfResponse(
            service('pdf')->render($view, compact('items', 'awal', 'akhir')),
            $filename,
        );
    }
}
