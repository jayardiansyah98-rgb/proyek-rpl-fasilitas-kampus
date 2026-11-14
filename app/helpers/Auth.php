<?php
/**
 * Auth Helper & Middleware RBAC
 * Sistem Informasi Pendataan Fasilitas STMIK IKMI Cirebon
 */

class Auth {
    /**
     * Memeriksa apakah pengguna sudah login dan memvalidasi masa aktif sesi (Inactivity Timeout)
     */
    public static function check() {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            return false;
        }

        // Pengecekan Session Inactivity Expiration
        $timeout = defined('SESSION_TIMEOUT') ? (int)SESSION_TIMEOUT : 7200;
        $lastActivity = $_SESSION['user']['last_activity'] ?? $_SESSION['user']['logged_at'] ?? 0;

        if ($lastActivity > 0 && (time() - $lastActivity) > $timeout) {
            // Sesi kedaluwarsa
            self::logout();
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_start();
            }
            $_SESSION['flash_error'] = 'Sesi Anda telah kedaluwarsa karena tidak ada aktivitas. Silakan masuk kembali.';
            return false;
        }

        // Perbarui waktu aktivitas terakhir
        $_SESSION['user']['last_activity'] = time();

        return true;
    }

    /**
     * Mendapatkan data pengguna yang sedang login
     */
    public static function user() {
        if (self::check()) {
            return $_SESSION['user'];
        }
        return null;
    }

    /**
     * Mendapatkan ID pengguna yang sedang login
     */
    public static function id() {
        $user = self::user();
        return $user ? $user['id'] : null;
    }

    /**
     * Mendapatkan nama role pengguna yang sedang login (e.g. 'admin', 'petugas_sarpras', 'pengguna')
     */
    public static function role() {
        $user = self::user();
        return $user ? $user['nama_role'] : null;
    }

    /**
     * Memeriksa apakah pengguna memiliki role tertentu
     * @param string|array $roles Role tunggal atau array beberapa role yang diizinkan
     */
    public static function hasRole($roles) {
        if (!self::check()) {
            return false;
        }

        $userRole = self::role();
        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }

        return $userRole === $roles;
    }

    /**
     * Memulai sesi login pengguna dan regenerasi session id untuk keamanan (Session Fixation Prevention)
     */
    public static function login(array $user) {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        // Regenerasi ID sesi jika sesi aktif
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'role_id' => $user['role_id'],
            'nama_role' => $user['nama_role'],
            'nomor_induk' => $user['nomor_induk'],
            'nama_lengkap' => $user['nama_lengkap'],
            'email' => $user['email'],
            'jenis_pengguna' => $user['jenis_pengguna'],
            'telepon' => $user['telepon'] ?? '',
            'logged_at' => time(),
            'last_activity' => time()
        ];
    }

    /**
     * Mengakhiri sesi pengguna (Logout)
     */
    public static function logout() {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Middleware Guard: Mengharuskan pengguna sudah login
     * Jika belum login, redirect ke halaman login
     */
    public static function requireLogin() {
        if (!self::check()) {
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_start();
            }
            $_SESSION['flash_error'] = 'Silakan masuk (login) terlebih dahulu untuk mengakses halaman tersebut.';
            redirect('auth/login');
            exit;
        }
    }

    /**
     * Middleware Guard: Mengharuskan pengguna memiliki role tertentu
     * Jika tidak memiliki hak akses, tolak akses dan tampilkan HTTP 403 Forbidden
     * @param string|array $roles
     */
    public static function requireRole($roles) {
        self::requireLogin();

        if (!self::hasRole($roles)) {
            http_response_code(403);
            require_once __DIR__ . '/../controllers/ErrorController.php';
            $errorCtrl = new ErrorController();
            $errorCtrl->forbidden([
                'required_roles' => (array)$roles,
                'current_role' => self::role()
            ]);
            exit;
        }
    }
}
