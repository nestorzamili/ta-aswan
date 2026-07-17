<?php

namespace App\Controllers;

use App\Models\SupplierModel;

class SupplierController extends BaseController
{
    private const ROUTE_INDEX = '/supplier';

    protected SupplierModel $model;

    public function __construct()
    {
        $this->model = new SupplierModel();
    }

    public function index()
    {
        $q       = trim((string) $this->request->getGet('q'));
        $perPage = $this->perPage();
        $builder = $this->model->orderBy('id_supplier', 'DESC');
        if ($q !== '') {
            $builder->groupStart()
                ->like('nama_supplier', $q)
                ->orLike('alamat', $q)
                ->orLike('telepon', $q)
                ->orLike('email', $q)
                ->groupEnd();
        }

        $items = $builder->paginate($perPage);
        $ids   = array_column($items, 'id_supplier');
        $usage = $this->usageCounts($ids);

        foreach ($items as &$row) {
            $row['trx_count'] = $usage[(int) $row['id_supplier']] ?? 0;
        }
        unset($row);

        return view('supplier/index', [
            'title'   => 'Supplier',
            'items'   => $items,
            'pager'   => $this->model->pager,
            'q'       => $q,
            'perPage' => $perPage,
        ]);
    }

    public function show($id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        $db    = db_connect();
        $stats = $db->table('barang_masuk')
            ->select('COUNT(*) AS jml_trx, COALESCE(SUM(total_quantity),0) AS total_qty, COALESCE(SUM(total_harga),0) AS total_nilai')
            ->where('id_supplier', (int) $id)
            ->get()
            ->getRowArray() ?? ['jml_trx' => 0, 'total_qty' => 0, 'total_nilai' => 0];

        $recent = $db->table('barang_masuk bm')
            ->select('bm.id_masuk, bm.no_faktur, bm.tanggal_masuk, bm.total_quantity, bm.total_harga, a.nama as nama_admin')
            ->join('admin a', 'a.id_admin = bm.id_admin', 'left')
            ->where('bm.id_supplier', (int) $id)
            ->orderBy('bm.tanggal_masuk', 'DESC')
            ->orderBy('bm.id_masuk', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();

        return view('supplier/show', [
            'title'  => 'Detail Supplier',
            'item'   => $item,
            'stats'  => $stats,
            'recent' => $recent,
        ]);
    }

    public function create()
    {
        return view('supplier/form', ['title' => 'Tambah Supplier', 'item' => null]);
    }

    public function store()
    {
        $data = $this->collect();
        if ($errors = $this->validateSupplier($data)) {
            return $this->redirectWithFieldErrors(self::ROUTE_INDEX . '/create', $errors);
        }
        $this->model->insert($data);

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Supplier ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        return view('supplier/form', ['title' => 'Edit Supplier', 'item' => $item]);
    }

    public function update($id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        $data = $this->collect();
        if ($errors = $this->validateSupplier($data, (int) $id)) {
            return $this->redirectWithFieldErrors(self::ROUTE_INDEX . '/' . $id . '/edit', $errors);
        }
        $this->model->update($id, $data);

        return redirect()->to(self::ROUTE_INDEX . '/' . $id)->with('success', 'Supplier diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        $used = db_connect()->table('barang_masuk')->where('id_supplier', $id)->countAllResults();
        if ($used > 0) {
            return redirect()->to(self::ROUTE_INDEX)->with(
                'error',
                'Supplier tidak dapat dihapus: sudah dipakai di ' . $used . ' transaksi masuk.',
            );
        }
        $this->model->delete($id);

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Supplier dihapus.');
    }

    /**
     * @param list<int|string> $ids
     *
     * @return array<int, int>
     */
    protected function usageCounts(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $rows = db_connect()->table('barang_masuk')
            ->select('id_supplier, COUNT(*) AS jml')
            ->whereIn('id_supplier', $ids)
            ->groupBy('id_supplier')
            ->get()
            ->getResultArray();

        $out = [];

        foreach ($rows as $r) {
            $out[(int) $r['id_supplier']] = (int) $r['jml'];
        }

        return $out;
    }

    protected function collect(): array
    {
        $telepon = preg_replace('/\s+/', '', trim((string) $this->request->getPost('telepon'))) ?? '';

        return [
            'nama_supplier' => trim((string) $this->request->getPost('nama_supplier')),
            'alamat'        => trim((string) $this->request->getPost('alamat')),
            'telepon'       => $telepon !== '' ? $telepon : null,
            'email'         => trim((string) $this->request->getPost('email')) ?: null,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validateSupplier(array $data, ?int $ignoreId = null): array
    {
        $errors = [];
        if ($data['nama_supplier'] === '') {
            $errors['nama_supplier'] = 'Nama supplier wajib diisi.';
        } elseif (mb_strlen($data['nama_supplier']) > 100) {
            $errors['nama_supplier'] = 'Nama supplier maksimal 100 karakter.';
        } else {
            $dup = $this->model->where('nama_supplier', $data['nama_supplier']);
            if ($ignoreId !== null) {
                $dup = $dup->where('id_supplier !=', $ignoreId);
            }
            if ($dup->first()) {
                $errors['nama_supplier'] = 'Nama supplier sudah terdaftar.';
            }
        }

        if (! empty($data['telepon']) && ! preg_match('/^[0-9+()\-]{6,20}$/', (string) $data['telepon'])) {
            $errors['telepon'] = 'Format telepon tidak valid.';
        }

        if (! empty($data['email']) && ! filter_var((string) $data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }

        return $errors;
    }
}
