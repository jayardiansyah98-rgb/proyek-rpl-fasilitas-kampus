<?php
/**
 * KategoriController
 * Modul Master Kategori Fasilitas (KHUSUS ROLE ADMIN)
 * Menggunakan KategoriModel untuk akses data database.
 */

class KategoriController extends Controller {
    protected $kategoriModel;

    public function __construct() {
        Auth::requireRole('admin');
        $this->kategoriModel = $this->model('KategoriModel');
    }

    public function index() {
        $kategori = $this->kategoriModel->getAll();

        $data = [
            'title' => 'Kategori Fasilitas - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'kategori_list' => $kategori
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('kategori/index', $data);
        $this->view('layouts/dashboard_footer', $data);
    }
}
