<?php

namespace App\Controllers;

use App\Models\AdminModel;

class ProfileController extends BaseController
{
    private const ROUTE_INDEX = '/profil';

    private const MAX_FOTO_MB = 2;

    private const ALLOWED_FOTO_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    protected AdminModel $model;

    public function __construct()
    {
        $this->model = new AdminModel();
    }

    public function index()
    {
        $item = $this->model->find(session('id_admin'));
        if (! $item) {
            return redirect()->to('/dashboard')->with('error', self::MSG_NOT_FOUND);
        }

        return view('profile/index', [
            'title' => 'Profil Saya',
            'item'  => $item,
        ]);
    }

    public function update()
    {
        $id = (int) session('id_admin');
        if (! $this->model->find($id)) {
            return redirect()->to('/dashboard')->with('error', self::MSG_NOT_FOUND);
        }

        $data = [
            'nama'          => trim((string) $this->request->getPost('nama')),
            'email'         => trim((string) $this->request->getPost('email')),
            'nomor_telepon' => trim((string) $this->request->getPost('nomor_telepon')),
        ];

        $errors = $this->validateProfile($data, $id);
        if ($errors !== []) {
            return $this->redirectBackWithFieldErrors($errors);
        }

        $this->model->update($id, $data);
        session()->set('nama', $data['nama']);

        log_activity('update', 'profil', $id, 'Memperbarui profil');

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateFoto()
    {
        $id   = (int) session('id_admin');
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/dashboard')->with('error', self::MSG_NOT_FOUND);
        }

        $file = $this->request->getFile('foto');
        if ($file === null || ! $file->isValid()) {
            return $this->redirectBackWithFieldErrors(['foto' => 'Berkas foto tidak valid.']);
        }
        if ($file->getSizeByUnit('mb') > self::MAX_FOTO_MB) {
            return $this->redirectBackWithFieldErrors(['foto' => 'Ukuran foto maksimal ' . self::MAX_FOTO_MB . ' MB.']);
        }
        if (! in_array($file->getMimeType(), self::ALLOWED_FOTO_MIME, true)) {
            return $this->redirectBackWithFieldErrors(['foto' => 'Format harus JPG, PNG, atau WebP.']);
        }

        $dir = rtrim(WRITEPATH, '/\\') . '/uploads/avatars';
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return $this->redirectBackWithFieldErrors(['foto' => 'Gagal menyiapkan folder unggahan.']);
        }

        $newName = $file->getRandomName();
        $file->move($dir, $newName);

        $this->deleteFotoFile($user['foto'] ?? null);
        $this->model->update($id, ['foto' => $newName]);
        session()->set('foto', $newName);

        log_activity('update', 'profil', $id, 'Memperbarui foto profil');

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Foto profil diperbarui.');
    }

    public function deleteFoto()
    {
        $id   = (int) session('id_admin');
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/dashboard')->with('error', self::MSG_NOT_FOUND);
        }

        $this->deleteFotoFile($user['foto'] ?? null);
        $this->model->update($id, ['foto' => null]);
        session()->remove('foto');

        log_activity('update', 'profil', $id, 'Menghapus foto profil');

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Foto profil dihapus.');
    }

    public function serveFoto()
    {
        $user = $this->model->find(session('id_admin'));
        $name = basename((string) ($user['foto'] ?? ''));
        $path = rtrim(WRITEPATH, '/\\') . '/uploads/avatars/' . $name;
        if ($name === '' || ! is_file($path)) {
            return $this->response->setStatusCode(404);
        }

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path) ?: 'application/octet-stream')
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody((string) file_get_contents($path));
    }

    private function deleteFotoFile(?string $name): void
    {
        $safe = basename((string) $name);
        if ($safe === '') {
            return;
        }
        $path = rtrim(WRITEPATH, '/\\') . '/uploads/avatars/' . $safe;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function updatePassword()
    {
        $id   = (int) session('id_admin');
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/dashboard')->with('error', self::MSG_NOT_FOUND);
        }

        $passwordLama    = (string) $this->request->getPost('password_lama');
        $password        = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');

        $errors = [];
        if (! password_verify($passwordLama, $user['password'])) {
            $errors['password_lama'] = 'Password lama tidak sesuai.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Password baru minimal 6 karakter.';
        }
        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Konfirmasi password tidak cocok.';
        }

        if ($errors !== []) {
            return $this->redirectBackWithFieldErrors($errors);
        }

        $this->model->update($id, ['password' => $password]);

        log_activity('update', 'profil', $id, 'Mengubah password');

        return redirect()->to(self::ROUTE_INDEX)->with('success', 'Password berhasil diubah.');
    }

    /**
     * @param array<string, string> $data
     *
     * @return array<string, string>
     */
    protected function validateProfile(array $data, int $id): array
    {
        $errors = [];
        if ($data['nama'] === '') {
            $errors['nama'] = 'Nama wajib diisi.';
        }
        if ($data['email'] === '') {
            $errors['email'] = 'Email wajib diisi.';
        } elseif (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif ($this->model->where('email', $data['email'])->where('id_admin !=', $id)->first()) {
            $errors['email'] = 'Email sudah digunakan.';
        }

        return $errors;
    }
}
