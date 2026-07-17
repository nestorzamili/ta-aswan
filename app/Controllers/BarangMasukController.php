<?php

namespace App\Controllers;

use App\Models\BarangMasukModel;
use App\Models\BarangModel;
use App\Models\SupplierModel;
use Throwable;

class BarangMasukController extends BaseController
{
    private const ROUTE_INDEX  = '/barang-masuk';
    private const ROUTE_PREFIX = '/barang-masuk/';

    public function index()
    {
        $q          = trim((string) $this->request->getGet('q'));
        $awal       = $this->parseDateFilter($this->request->getGet('tanggal_awal'));
        $akhir      = $this->parseDateFilter($this->request->getGet('tanggal_akhir'));
        $idSupplier = (int) $this->request->getGet('id_supplier');
        $perPage    = $this->perPage();

        $model = new BarangMasukModel();
        $model->select('barang_masuk.*, s.nama_supplier, a.nama as nama_admin')
            ->join('supplier s', 's.id_supplier = barang_masuk.id_supplier')
            ->join('admin a', 'a.id_admin = barang_masuk.id_admin')
            ->orderBy('barang_masuk.id_masuk', 'DESC');

        if ($q !== '') {
            $model->groupStart()
                ->like('barang_masuk.no_faktur', $q)
                ->orLike('s.nama_supplier', $q)
                ->groupEnd();
        }
        if ($awal !== '') {
            $model->where('barang_masuk.tanggal_masuk >=', $awal);
        }
        if ($akhir !== '') {
            $model->where('barang_masuk.tanggal_masuk <=', $akhir);
        }
        if ($idSupplier > 0) {
            $model->where('barang_masuk.id_supplier', $idSupplier);
        }

        return view('barang_masuk/index', [
            'title'         => 'Barang Masuk',
            'items'         => $model->paginate($perPage),
            'pager'         => $model->pager,
            'q'             => $q,
            'tanggal_awal'  => $this->formatDateDisplay($awal) ?: trim((string) $this->request->getGet('tanggal_awal')),
            'tanggal_akhir' => $this->formatDateDisplay($akhir) ?: trim((string) $this->request->getGet('tanggal_akhir')),
            'id_supplier'   => $idSupplier,
            'suppliers'     => (new SupplierModel())->orderBy('nama_supplier')->findAll(),
            'perPage'       => $perPage,
        ]);
    }

    public function create()
    {
        return view('barang_masuk/form', [
            'title'     => 'Tambah Barang Masuk',
            'no_faktur' => '[Otomatis]',
            'suppliers' => (new SupplierModel())->orderBy('nama_supplier')->findAll(),
            'barang'    => (new BarangModel())->orderBy('nama_barang')->findAll(),
        ]);
    }

    public function store()
    {
        $idSupplier = (int) $this->request->getPost('id_supplier');
        if ($idSupplier <= 0 || ! (new SupplierModel())->find($idSupplier)) {
            return $this->redirectWithFieldErrors(self::ROUTE_INDEX . '/create', [
                'id_supplier' => 'Supplier tidak valid atau sudah dihapus.',
            ]);
        }

        try {
            $lines = service('transactionLines')->parseFromRequest(
                $this->request,
                true,
                fn ($v) => $this->parseMoney($v),
            );
            $idMasuk = service('transactions')->processBarangMasuk([
                'tanggal_masuk' => $this->parseDateFilter($this->request->getPost('tanggal_masuk')) ?: date('Y-m-d'),
                'id_supplier'   => $idSupplier,
                'id_admin'      => (int) session('id_admin'),
            ], $lines);
        } catch (Throwable $e) {
            return $this->redirectWithFieldErrors(self::ROUTE_INDEX . '/create', [
                'lines' => $e->getMessage(),
            ], $e->getMessage());
        }

        return redirect()->to(self::ROUTE_PREFIX . $idMasuk)->with('success', 'Barang masuk disimpan. Stok diperbarui.');
    }

    public function show($id)
    {
        $data = $this->loadDetail((int) $id);
        if (! $data) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        return view('barang_masuk/show', $data + ['title' => 'Detail Barang Masuk']);
    }

    public function pdf($id)
    {
        $data = $this->loadDetail((int) $id);
        if (! $data) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        $no = (string) ($data['header']['no_faktur'] ?? $id);

        return $this->pdfResponse(
            service('pdf')->render('pdf/faktur_masuk', $data + ['docRef' => $no]),
            'Faktur-Masuk-' . $no . '.pdf',
        );
    }

    public function delete(int|string $id)
    {
        $data = $this->loadDetail((int) $id);
        if (! $data) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        try {
            service('transactions')->deleteBarangMasuk((int) $id, $data['details']);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Stok tidak cukup')) {
                $msg = 'Tidak dapat menghapus: stok sudah terpakai oleh transaksi lain. ' . $msg;
            }

            return redirect()->to(self::ROUTE_INDEX)->with('error', $msg);
        }

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Transaksi dihapus. Stok dikembalikan.');
    }

    protected function loadDetail(int $id): ?array
    {
        $header = db_connect()->table('barang_masuk bm')
            ->select('bm.*, s.nama_supplier, a.nama as nama_admin')
            ->join('supplier s', 's.id_supplier = bm.id_supplier')
            ->join('admin a', 'a.id_admin = bm.id_admin')
            ->where('bm.id_masuk', $id)
            ->get()->getRowArray();
        if (! $header) {
            return null;
        }
        $details = db_connect()->table('detail_masuk')->where('id_masuk', $id)->get()->getResultArray();

        return [
            'header'  => $header,
            'details' => service('barangLookup')->enrichDetails($details),
        ];
    }
}
