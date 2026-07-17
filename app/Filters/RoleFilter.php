<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $level   = session()->get('level');
        $allowed = $arguments ?? [];

        if (! $allowed || ! in_array($level, $allowed, true)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // FilterInterface requires after(); no post-response work needed.
    }
}
