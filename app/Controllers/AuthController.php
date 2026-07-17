<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\PasswordResetTokenModel;
use Config\Services;

class AuthController extends BaseController
{
    private const ROUTE_LOGIN         = '/login';
    private const MSG_BAD_CREDENTIALS = 'Username atau password salah.';
    private const DATETIME_FORMAT     = 'Y-m-d H:i:s';

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $throttler  = Services::throttler();
        $throttled  = ! $throttler->check('login_' . md5($this->request->getIPAddress()), 5, MINUTE);
        $validInput = $throttled || $this->validate([
            'username' => ['label' => 'Username', 'rules' => 'required'],
            'password' => ['label' => 'Password', 'rules' => 'required'],
        ]);

        if ($throttled || ! $validInput) {
            $response = $throttled
                ? $this->redirectBackWithFieldErrors([], 'Terlalu banyak percobaan login. Silakan coba lagi nanti.')
                : $this->redirectBackWithFieldErrors($this->validator->getErrors());
        } else {
            $username = trim((string) $this->request->getPost('username'));
            $password = (string) $this->request->getPost('password');
            $admin    = (new AdminModel())->findByUsername($username);

            if ($admin && $admin['status'] === 'aktif' && password_verify($password, $admin['password'])) {
                session()->regenerate();
                session()->set([
                    'isLoggedIn' => true,
                    'id_admin'   => $admin['id_admin'],
                    'username'   => $admin['username'],
                    'nama'       => $admin['nama'],
                    'level'      => $admin['level'],
                ]);
                $response = redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $admin['nama']);
            } else {
                $response = $this->redirectBackWithFieldErrors(
                    [
                        'username' => self::MSG_BAD_CREDENTIALS,
                        'password' => self::MSG_BAD_CREDENTIALS,
                    ],
                    self::MSG_BAD_CREDENTIALS,
                );
            }
        }

        return $response;
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(self::ROUTE_LOGIN);
    }

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        $email = trim((string) $this->request->getPost('email'));
        $admin = (new AdminModel())->findByEmail($email);

        if ($admin) {
            $token = bin2hex(random_bytes(32));
            (new PasswordResetTokenModel())->insert([
                'id_admin'   => $admin['id_admin'],
                'token'      => $token,
                'expires_at' => date(self::DATETIME_FORMAT, time() + 3600),
                'created_at' => date(self::DATETIME_FORMAT),
            ]);
            $link = base_url('reset-password/' . $token);

            $emailSvc = Services::email();
            $emailSvc->setTo($admin['email']);
            $emailSvc->setSubject('Permintaan Reset Password');
            $emailSvc->setMessage("<p>Halo {$admin['nama']},</p><p>Klik link berikut untuk melakukan reset password Anda:</p><p><a href=\"{$link}\">{$link}</a></p><p>Link ini berlaku selama 1 jam.</p>");
            if (! $emailSvc->send()) {
                log_message('error', $emailSvc->printDebugger(['headers']));
            }
        }

        return redirect()->to(self::ROUTE_LOGIN)->with(
            'success',
            'Jika email terdaftar, instruksi pemulihan sandi telah dikirim ke email tersebut.',
        );
    }

    public function resetPassword(string $token)
    {
        $row = (new PasswordResetTokenModel())
            ->where('token', $token)
            ->where('used_at', null)
            ->first();

        if (! $row || strtotime($row['expires_at']) < time()) {
            return redirect()->to(self::ROUTE_LOGIN)->with('error', 'Token reset tidak valid atau sudah kedaluwarsa.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    public function updatePassword(string $token)
    {
        $row = (new PasswordResetTokenModel())
            ->where('token', $token)
            ->where('used_at', null)
            ->first();

        if (! $row || strtotime($row['expires_at']) < time()) {
            return redirect()->to(self::ROUTE_LOGIN)->with('error', 'Token reset tidak valid atau sudah kedaluwarsa.');
        }

        if (! $this->validate([
            'password'         => ['label' => 'Password', 'rules' => 'required|min_length[6]'],
            'password_confirm' => ['label' => 'Konfirmasi Password', 'rules' => 'required|matches[password]'],
        ])) {
            return $this->redirectBackWithFieldErrors($this->validator->getErrors());
        }

        $password = (string) $this->request->getPost('password');

        (new AdminModel())->update($row['id_admin'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        (new PasswordResetTokenModel())->update($row['id'], [
            'used_at' => date(self::DATETIME_FORMAT),
        ]);

        return redirect()->to(self::ROUTE_LOGIN)->with('success', 'Password berhasil diubah. Silakan login.');
    }
}
