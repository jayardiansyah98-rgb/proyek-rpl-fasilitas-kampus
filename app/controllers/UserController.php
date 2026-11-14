<?php
/**
 * UserController
 * Modul Manajemen Pengguna Sistem (KHUSUS ROLE ADMIN)
 */

class UserController extends Controller {
    public function __construct() {
        // Proteksi Server-Side Ketat: Hanya Admin yang boleh mengakses
        Auth::requireRole('admin');
    }

    public function index() {
        $userModel = $this->model('UserModel');
        $users = $userModel->getAllUsers();

        $data = [
            'title' => 'Manajemen Pengguna - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'users_list' => $users
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('users/index', $data);
        $this->view('layouts/dashboard_footer', $data);
    }
}
