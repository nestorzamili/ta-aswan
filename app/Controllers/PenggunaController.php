<?php

namespace App\Controllers;

use App\Models\AdminModel;

class PenggunaController extends BaseController
{
    private const ROUTE_INDEX = '/pengguna';

    protected AdminModel $model;

    public function __construct()
    {
        $this->model = new AdminModel();
    }

    public function index()
    {
        $q      = trim((string) $this->request->getGet('q'));
        $level  = trim((string) $this->request->getGet('level'));
        $status = trim((string) $this->request->getGet('status'));

        if (! in_array($level, ['admin', 'karyawan'], true)) {
            $level = '';
        }
        if (! in_array($status, ['aktif', 'nonaktif'], true)) {
            $status = '';
        }

        $perPage = $this->perPage();
        $builder = $this->model->orderBy('id_admin', 'DESC');
        if ($q !== '') {
            $builder->groupStart()
                ->like('nama', $q)
                ->orLike('username', $q)
                ->orLike('email', $q)
                ->orLike('nomor_telepon', $q)
                ->groupEnd();
        }
        if ($level !== '') {
            $builder->where('level', $level);
        }
        if ($status !== '') {
            $builder->where('status', $status);
        }

        return view('pengguna/index', [
            'title'   => 'Pengguna',
            'items'   => $builder->paginate($perPage),
            'pager'   => $this->model->pager,
            'q'       => $q,
            'level'   => $level,
            'status'  => $status,
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('pengguna/form', ['title' => 'Tambah Pengguna', 'item' => null]);
    }

    public function store()
    {
        $data = $this->collect(true, null);
        if (isset($data['_errors'])) {
            return $this->redirectBackWithFieldErrors($data['_errors']);
        }
        $this->model->insert($data);

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Pengguna ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (! $item) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', self::MSG_NOT_FOUND);
        }

        return view('pengguna/form', ['title' => 'Edit Pengguna', 'item' => $item]);
    }

    public function update($id)
    {
        $data = $this->collect(false, (int) $id);
        if (isset($data['_errors'])) {
            return $this->redirectBackWithFieldErrors($data['_errors']);
        }
        $this->model->update($id, $data);

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Pengguna diperbarui.');
    }

    public function delete($id)
    {
        if ((int) $id === (int) session('id_admin')) {
            return redirect()->to(self::ROUTE_INDEX)->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        $this->model->delete($id);

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Pengguna dihapus.');
    }

    protected function collect(bool $requirePassword, ?int $ignoreId = null): array
    {
        $data = [
            'username'      => trim((string) $this->request->getPost('username')),
            'nama'          => trim((string) $this->request->getPost('nama')),
            'email'         => trim((string) $this->request->getPost('email')),
            'nomor_telepon' => trim((string) $this->request->getPost('nomor_telepon')),
            'level'         => $this->request->getPost('level') === 'admin' ? 'admin' : 'karyawan',
            'status'        => $this->request->getPost('status') === 'nonaktif' ? 'nonaktif' : 'aktif',
        ];

        $errors = $this->validateProfileFields($data);
        $this->assertUniqueField($errors, 'username', $data['username'], $ignoreId, 'Username sudah digunakan.');
        $this->assertUniqueField($errors, 'email', $data['email'], $ignoreId, 'Email sudah digunakan.');
        $this->applyPasswordRules($data, $errors, $requirePassword);

        if ($errors !== []) {
            return ['_errors' => $errors];
        }

        return $data;
    }

    /**
     * @param array<string, string> $data
     *
     * @return array<string, string>
     */
    protected function validateProfileFields(array $data): array
    {
        $errors = [];
        if ($data['nama'] === '') {
            $errors['nama'] = 'Nama wajib diisi.';
        }
        if ($data['username'] === '') {
            $errors['username'] = 'Username wajib diisi.';
        }
        if ($data['email'] === '') {
            $errors['email'] = 'Email wajib diisi.';
        } elseif (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        }

        return $errors;
    }

    /**
     * @param array<string, string> $errors
     */
    protected function assertUniqueField(array &$errors, string $field, string $value, ?int $ignoreId, string $message): void
    {
        if (isset($errors[$field]) || $value === '') {
            return;
        }

        $query = $this->model->where($field, $value);
        if ($ignoreId !== null) {
            $query = $query->where('id_admin !=', $ignoreId);
        }
        if ($query->first()) {
            $errors[$field] = $message;
        }
    }

    /**
     * @param array<string, mixed>  $data
     * @param array<string, string> $errors
     */
    protected function applyPasswordRules(array &$data, array &$errors, bool $requirePassword): void
    {
        $password = (string) $this->request->getPost('password');
        if (! $requirePassword && $password === '') {
            return;
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Password minimal 6 karakter.';

            return;
        }
        $data['password'] = $password;
    }
}
