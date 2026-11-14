<?php
/**
 * ErrorController
 * Menangani halaman 404 Not Found & 403 Forbidden (RBAC Guard)
 */

class ErrorController extends Controller {
    public function index() {
        http_response_code(404);
        $data = [
            'title' => '404 - Halaman Tidak Ditemukan'
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('errors/404', $data);
        $this->view('layouts/footer', $data);
    }

    public function forbidden($params = []) {
        http_response_code(403);
        $data = [
            'title' => '403 - Akses Ditolak (Forbidden)',
            'required_roles' => $params['required_roles'] ?? [],
            'current_role' => $params['current_role'] ?? Auth::role()
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('errors/403', $data);
        $this->view('layouts/footer', $data);
    }
}
