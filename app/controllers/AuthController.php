<?php
/**
 * AuthController
 * Menangani alur login, verifikasi kredensial, proteksi sesi, dan logout
 */

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('UserModel');
    }

    public function index() {
        $this->login();
    }

    /**
     * Menampilkan form login (GET) atau memproses submit otentikasi (POST)
     */
    public function login() {
        // Jika sudah login, langsung alihkan ke dashboard sesuai role
        if (Auth::check()) {
            redirect('dashboard');
            return;
        }

        $error = null;
        $identifier = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $error = 'Sesi formulir telah kadaluarsa atau token tidak valid (CSRF). Silakan muat ulang halaman.';
            } else {
                $identifier = trim($_POST['identifier'] ?? '');
                $password = $_POST['password'] ?? '';

                if (empty($identifier) || empty($password)) {
                    $error = 'Email/Nomor Induk dan kata sandi wajib diisi.';
                } else {
                    $validation = $this->userModel->validateCredentials($identifier, $password);

                if ($validation['success']) {
                    // Set session login dengan session_regenerate_id(true)
                    Auth::login($validation['user']);

                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['flash_success'] = 'Selamat datang kembali, ' . htmlspecialchars($validation['user']['nama_lengkap']) . '!';

                    // Redirect ke dashboard
                    redirect('dashboard');
                    return;
                } else {
                    $error = $validation['message'];
                }
            }
        }
    }

        $data = [
            'title' => 'Login - Sistem Informasi Fasilitas STMIK IKMI Cirebon',
            'error' => $error,
            'old_identifier' => $identifier
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('auth/login', $data);
        $this->view('layouts/footer', $data);
    }

    /**
     * Menghancurkan sesi pengguna (Logout)
     */
    public function logout() {
        Auth::logout();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_success'] = 'Anda telah berhasil keluar dari sistem.';

        redirect('auth/login');
    }

    /**
     * Endpoint API status sesi autentikasi pengguna untuk integrasi chatbot
     * Mengembalikan data sesi server-side aktual yang aman tanpa mempercayai input browser
     */
    public function sessionStatus() {
        header('Content-Type: application/json');

        $sid = $_GET['sid'] ?? '';
        if (!empty($sid) && preg_match('/^[a-zA-Z0-9,-]{16,128}$/', $sid)) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            session_id($sid);
            session_start();
        } elseif (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $authenticated = Auth::check();
        $user = $authenticated ? Auth::user() : null;

        if ($authenticated && $user) {
            echo json_encode([
                'status' => 'success',
                'authenticated' => true,
                'session_id' => session_id(),
                'user' => [
                    'id' => (int)$user['id'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'nama_role' => $user['nama_role'],
                    'jenis_pengguna' => $user['jenis_pengguna'],
                    'nomor_induk' => $user['nomor_induk']
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'authenticated' => false,
                'session_id' => session_id(),
                'user' => null
            ]);
        }
        exit;
    }
}
