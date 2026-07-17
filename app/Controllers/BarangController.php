<?php

namespace App\Controllers;

use App\Models\BarangModel;
use CodeIgniter\Model;

abstract class BarangController extends BaseController
{
    protected string $tipe;

    abstract protected function cfg(): array;

    protected function model(): Model
    {
        return new BarangModel();
    }

    protected function nextKode(): string
    {
        $n = service('numbering');

        return $this->tipe === 'aksesoris' ? $n->nextAksesoris() : $n->nextSparepart();
    }

    protected function findForTipe(int $id): ?array
    {
        return $this->model()
            ->where('tipe_barang', $this->tipe)
            ->find($id);
    }

    public function index()
    {
        $c        = $this->cfg();
        $q        = trim((string) $this->request->getGet('q'));
        $kategori = trim((string) $this->request->getGet('kategori'));
        $merk     = trim((string) $this->request->getGet('merk'));
        $status   = trim((string) $this->request->getGet('status_stok'));
        $perPage  = $this->perPage();
        $m        = $this->model();

        $builder = $m->where('tipe_barang', $this->tipe)->orderBy($c['pk'], 'DESC');
        if ($q !== '') {
            $builder->groupStart()
                ->like($c['nama'], $q)
                ->orLike($c['kode'], $q)
                ->orLike('kode_manual', $q)
                ->groupEnd();
        }
        if ($kategori !== '') {
            $builder->where('kategori', $kategori);
        }
        if ($merk !== '') {
            $builder->where('merk', $merk);
        }
        if ($status !== '') {
            $builder->where('status_stok', $status);
        }

        return view('barang/index', [
            'title'     => 'Data ' . $c['label'],
            'cfg'       => $c,
            'items'     => $builder->paginate($perPage),
            'pager'     => $m->pager,
            'q'         => $q,
            'kategori'  => $kategori,
            'merk'      => $merk,
            'status'    => $status,
            'perPage'   => $perPage,
            'kategoris' => array_column($m->db->table($m->table)->select('kategori')->distinct()->where('tipe_barang', $this->tipe)->where('kategori !=', '')->get()->getResultArray(), 'kategori'),
            'merks'     => array_column($m->db->table($m->table)->select('merk')->distinct()->where('tipe_barang', $this->tipe)->where('merk !=', '')->get()->getResultArray(), 'merk'),
        ]);
    }

    public function create()
    {
        $c = $this->cfg();

        return view('barang/form', [
            'title' => 'Tambah ' . $c['label'],
            'cfg'   => $c,
            'item'  => null,
            'kode'  => '[Otomatis]',
        ]);
    }

    public function store()
    {
        $c    = $this->cfg();
        $data = $this->collect(true);
        if ($errors = $this->validateBarang($data, true)) {
            return $this->redirectWithFieldErrors('/' . $c['route'] . '/create', $errors);
        }
        $data['tipe_barang'] = $this->tipe;
        $data[$c['kode']]    = $this->nextKode();
        $data                = service('stock')->setStatusForRow($data);
        $this->model()->insert($data);

        return redirect()->to('/' . $c['route'])->with('success', $c['label'] . ' berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $c    = $this->cfg();
        $item = $this->findForTipe((int) $id);
        if (! $item) {
            return redirect()->to('/' . $c['route'])->with('error', self::MSG_NOT_FOUND);
        }

        return view('barang/form', [
            'title' => 'Edit ' . $c['label'],
            'cfg'   => $c,
            'item'  => $item,
            'kode'  => $item[$c['kode']],
        ]);
    }

    public function update($id)
    {
        $c    = $this->cfg();
        $item = $this->findForTipe((int) $id);
        if (! $item) {
            return redirect()->to('/' . $c['route'])->with('error', self::MSG_NOT_FOUND);
        }
        $data = $this->collect(false);
        if ($errors = $this->validateBarang($data, false)) {
            return $this->redirectWithFieldErrors('/' . $c['route'] . '/' . $id . '/edit', $errors);
        }
        $data[$c['kode']] = $item[$c['kode']];
        $this->model()->update($id, $data);

        return redirect()->to('/' . $c['route'])->with('success', $c['label'] . ' diperbarui.');
    }

    public function delete($id)
    {
        $c    = $this->cfg();
        $item = $this->findForTipe((int) $id);
        if (! $item) {
            return redirect()->to('/' . $c['route'])->with('error', self::MSG_NOT_FOUND);
        }
        if (method_exists($this->model(), 'hasTransactionHistory')
            && $this->model()->hasTransactionHistory((int) $id)) {
            return redirect()->to('/' . $c['route'])
                ->with('error', 'Tidak dapat dihapus: sudah ada riwayat transaksi.');
        }
        $this->model()->delete($id);

        return redirect()->to('/' . $c['route'])->with('success', $c['label'] . ' dihapus.');
    }

    protected function collect(bool $includeStok): array
    {
        $c = $this->cfg();

        $data = [
            'kode_manual' => $this->request->getPost('kode_manual') ?: null,
            $c['nama']    => trim((string) $this->request->getPost($c['nama'])),
            'kategori'    => trim((string) $this->request->getPost('kategori')),
            'merk'        => trim((string) $this->request->getPost('merk')),
            'satuan'      => trim((string) $this->request->getPost('satuan')) ?: 'pcs',
            'harga_beli'  => $this->parseMoney($this->request->getPost('harga_beli')),
            'harga_jual'  => $this->parseMoney($this->request->getPost('harga_jual')),
        ];

        if ($includeStok) {
            $data['stok'] = max(0, (int) $this->request->getPost('stok'));
        }

        return $data;
    }

    protected function validateBarang(array $data, bool $includeStok): array
    {
        $c      = $this->cfg();
        $errors = [];

        if ($data[$c['nama']] === '') {
            $errors[$c['nama']] = 'Nama ' . strtolower($c['label']) . ' wajib diisi.';
        }
        if ((int) $data['harga_beli'] < 0) {
            $errors['harga_beli'] = 'Harga beli tidak boleh negatif.';
        }
        if ((int) $data['harga_jual'] < 0) {
            $errors['harga_jual'] = 'Harga jual tidak boleh negatif.';
        }
        if ($includeStok && (int) ($data['stok'] ?? 0) < 0) {
            $errors['stok'] = 'Stok tidak boleh negatif.';
        }

        return $errors;
    }
}
