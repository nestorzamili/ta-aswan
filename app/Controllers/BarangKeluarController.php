<?php

namespace App\Controllers;

use App\Models\BarangKeluarModel;
use App\Models\BarangModel;
use Throwable;

class BarangKeluarController extends BaseController
{
    private const ROUTE_INDEX  = '/barang-keluar';
    private const ROUTE_PREFIX = '/barang-keluar/';

    public function index()
    {
        $q       = trim((string) $this->request->getGet('q'));
        $awal    = $this->parseDateFilter($this->request->getGet('tanggal_awal'));
        $akhir   = $this->parseDateFilter($this->request->getGet('tanggal_akhir'));
        $perPage = $this->perPage();

        $model = new BarangKeluarModel();
        $model->select('barang_keluar.*, a.nama as nama_admin')
            ->join('admin a', 'a.id_admin = barang_keluar.id_admin')
            ->orderBy('barang_keluar.id_keluar', 'DESC');

        if ($q !== '') {
            $model->groupStart()
                ->like('barang_keluar.no_transaksi', $q)
                ->orLike('barang_keluar.tujuan', $q)
                ->groupEnd();
        }
        if ($awal !== '') {
            $model->where('barang_keluar.tanggal_keluar >=', $awal);
        }
        if ($akhir !== '') {
            $model->where('barang_keluar.tanggal_keluar <=', $akhir);
        }

        return view('barang_keluar/index', [
            'title'         => 'Barang Keluar',
            'items'         => $model->paginate($perPage),
            'pager'         => $model->pager,
            'q'             => $q,
            'tanggal_awal'  => $this->formatDateDisplay($awal) ?: trim((string) $this->request->getGet('tanggal_awal')),
            'tanggal_akhir' => $this->formatDateDisplay($akhir) ?: trim((string) $this->request->getGet('tanggal_akhir')),
            'perPage'       => $perPage,
        ]);
    }

    public function create()
    {
        return view('barang_keluar/form', $this->formData([
            'title'        => 'Tambah Barang Keluar',
            'item'         => null,
            'details'      => [],
            'no_transaksi' => '[Otomatis]',
        ]));
    }

    public function store()
    {
        if (! $this->validate([
            'tujuan' => ['label' => 'Tujuan', 'rules' => 'required|max_length[100]'],
        ])) {
            return $this->redirectWithFieldErrors(self::ROUTE_INDEX . '/create', $this->validator->getErrors());
        }

        try {
            $lines = service('transactionLines')->parseFromRequest($this->request, false);
            $id    = service('transactions')->processBarangKeluar([
                'tanggal_keluar' => $this->parseDateFilter($this->request->getPost('tanggal_keluar')) ?: date('Y-m-d'),
                'tujuan'         => trim((string) $this->request->getPost('tujuan')),
                'id_admin'       => (int) session('id_admin'),
            ], $lines);
        } catch (Throwable $e) {
            return $this->redirectWithFieldErrors(self::ROUTE_INDEX . '/create', [
                'lines' => $e->getMessage(),
            ], $e->getMessage());
        }

        return redirect()->to(self::ROUTE_PREFIX . $id)->with('success', 'Barang keluar disimpan. Stok diperbarui.');
    }

    public function show($id)
    {
        $data = $this->loadDetail((int) $id);
        if (! $data) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        return view('barang_keluar/show', $data + ['title' => 'Detail Barang Keluar']);
    }

    public function edit($id)
    {
        $data = $this->loadDetail((int) $id);
        if (! $data) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        return view('barang_keluar/form', $this->formData([
            'title'        => 'Edit Barang Keluar',
            'item'         => $data['header'],
            'details'      => $data['details'],
            'no_transaksi' => $data['header']['no_transaksi'],
        ]));
    }

    public function update(int|string $id)
    {
        $existing = $this->loadDetail((int) $id);
        if (! $existing) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        $editUrl = self::ROUTE_PREFIX . $id . '/edit';
        if (! $this->validate([
            'tujuan' => ['label' => 'Tujuan', 'rules' => 'required|max_length[100]'],
        ])) {
            return $this->redirectWithFieldErrors($editUrl, $this->validator->getErrors());
        }

        try {
            $lines = service('transactionLines')->parseFromRequest($this->request, false);
            service('transactions')->updateBarangKeluar((int) $id, $existing['details'], [
                'tanggal_keluar' => $this->parseDateFilter($this->request->getPost('tanggal_keluar')) ?: date('Y-m-d'),
                'tujuan'         => trim((string) $this->request->getPost('tujuan')),
            ], $lines);
            $response = redirect()->to(self::ROUTE_PREFIX . $id)->with('success', 'Transaksi keluar diperbarui.');
        } catch (Throwable $e) {
            $response = $this->redirectWithFieldErrors($editUrl, [
                'lines' => $e->getMessage(),
            ], $e->getMessage());
        }

        return $response;
    }

    public function pdf($id)
    {
        $data = $this->loadDetail((int) $id);
        if (! $data) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        $no = (string) ($data['header']['no_transaksi'] ?? $id);

        return $this->pdfResponse(
            service('pdf')->render('pdf/faktur_keluar', $data + ['docRef' => $no]),
            'Faktur-Keluar-' . $no . '.pdf',
        );
    }

    public function delete($id)
    {
        $data = $this->loadDetail((int) $id);
        if (! $data) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        try {
            service('transactions')->deleteBarangKeluar((int) $id, $data['details']);
        } catch (Throwable $e) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', $e->getMessage());
        }

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Transaksi dihapus. Stok dikembalikan.');
    }

    protected function loadDetail(int $id): ?array
    {
        $header = db_connect()->table('barang_keluar bk')
            ->select('bk.*, a.nama as nama_admin')
            ->join('admin a', 'a.id_admin = bk.id_admin')
            ->where('bk.id_keluar', $id)
            ->get()->getRowArray();
        if (! $header) {
            return null;
        }
        $details = db_connect()->table('detail_keluar')->where('id_keluar', $id)->get()->getResultArray();

        return [
            'header'  => $header,
            'details' => service('barangLookup')->enrichDetails($details),
        ];
    }

    protected function formData(array $extra): array
    {
        return $extra + [
            'barang' => (new BarangModel())->orderBy('nama_barang')->findAll(),
        ];
    }
}
