<?php

namespace App\Controllers;

use Config\Services;

class StokController extends BaseController
{
    public function index()
    {
        $status = trim((string) $this->request->getGet('status_stok'));
        $q      = trim((string) $this->request->getGet('q'));
        if (! in_array($status, ['aman', 'rendah', 'habis', 'kritis'], true)) {
            $status = '';
        }
        $db = db_connect();

        $builder = $db->table('(SELECT tipe_barang AS tipe, id_barang AS id, kode_barang AS kode, nama_barang AS nama,
                   kategori, merk, stok, status_stok
            FROM barang
            WHERE deleted_at IS NULL) AS t');

        if ($status === 'kritis') {
            $builder->whereIn('status_stok', ['rendah', 'habis']);
        } elseif ($status !== '') {
            $builder->where('status_stok', $status);
        }
        if ($q !== '') {
            $builder->groupStart()
                ->like('nama', $q)
                ->orLike('kode', $q)
                ->groupEnd();
        }

        $pager   = Services::pager();
        $page    = max(1, (int) ($this->request->getVar('page') ?? 1));
        $perPage = $this->perPage();

        $total = $builder->countAllResults(false);
        $builder->limit($perPage, ($page - 1) * $perPage);
        $rows = $builder->get()->getResultArray();

        return view('stok/index', [
            'title'   => 'Monitoring Stok',
            'items'   => $rows,
            'status'  => $status,
            'q'       => $q,
            'pager'   => $pager->makeLinks($page, $perPage, $total, 'default_full'),
            'page'    => $page,
            'perPage' => $perPage,
        ]);
    }
}
