<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Auth extends Controller
{
    public function login()
    {
        // Jika sudah login, lempar ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/admin/dashboard');
        }

        return view('auth/login');
    }

    public function processLogin()
    {
        $session = session();
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (empty($email) || empty($password)) {
            $session->setFlashdata('error', 'Email dan password wajib diisi.');
            return redirect()->to('/login')->withInput();
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user) {
            // Verifikasi password hash
            if (password_verify($password, $user->password_hash)) {
                $sessionData = [
                    'id'        => $user->id,
                    'name'      => $user->nama,
                    'email'     => $user->email,
                    'role'      => $user->role,
                    'logged_in' => true,
                ];
                $session->set($sessionData);
                return redirect()->to('/admin/dashboard');
            } else {
                $session->setFlashdata('error', 'Password salah.');
                return redirect()->to('/login')->withInput();
            }
        } else {
            $session->setFlashdata('error', 'Email tidak ditemukan.');
            return redirect()->to('/login')->withInput();
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login');
    }
}
