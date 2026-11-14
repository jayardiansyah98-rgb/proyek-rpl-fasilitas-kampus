<?php
/**
 * HomeController
 * Menangani halaman utama/landing page dan ringkasan status sistem
 * Menggunakan DashboardModel untuk mematuhi kaidah arsitektur MVC.
 */

class HomeController extends Controller {
    public function index() {
        // Ambil data ringkasan untuk dashboard landing page via Model
        $dashboardModel = $this->model('DashboardModel');
        $stats = $dashboardModel->getLandingStats();

        $data = [
            'title' => 'Beranda - Sistem Informasi Fasilitas Kampus STMIK IKMI Cirebon',
            'stats' => $stats
        ];

        $this->view('layouts/header', $data);
        $this->view('layouts/navbar', $data);
        $this->view('home/index', $data);
        $this->view('layouts/footer', $data);
    }
}
