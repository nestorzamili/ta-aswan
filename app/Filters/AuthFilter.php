<?php

namespace App\Filters;

use App\Models\AdminModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $admin = model(AdminModel::class)->find(session()->get('id_admin'));
        if (! $admin || $admin['status'] !== 'aktif') {
            session()->destroy();

            return redirect()->to('/login')->with('error', 'Akun Anda telah dinonaktifkan atau tidak ditemukan.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // FilterInterface requires after(); no post-response work needed.
    }
}
