<?php
/**
 * DashboardController
 * Menangani orkestrasi metrik, visualisasi analitik grafik Chart.js,
 * serta dashboard berbasis peran (Admin, Petugas Sarpras, dan Dosen/Mahasiswa).
 * 
 * Mengikuti Arsitektur MVC murni:
 * Controller tidak berisi query SQL langsung. Seluruh interaksi database
 * didelegasikan ke DashboardModel.
 */

class DashboardController extends Controller {
    protected $dashboardModel;

    public function __construct() {
        Auth::requireLogin();
        $this->dashboardModel = $this->model('DashboardModel');
    }

    public function index() {
        $user = Auth::user();
        $role = $user['nama_role'];

        $data = [
            'title' => 'Dashboard - ' . APP_NAME,
            'user' => $user,
            'role' => $role
        ];

        // 1. DASHBOARD ADMIN & PETUGAS SARPRAS
        if ($role === 'admin' || $role === 'petugas_sarpras') {
            $petugasId = ($role === 'petugas_sarpras') ? (int)$user['id'] : null;

            // A. Metrik Statistik Utama
            $data['stats'] = $this->dashboardModel->getAdminStats($petugasId);

            // B. Distribusi Kondisi Fasilitas (Untuk Chart.js Doughnut)
            $data['distribusi_kondisi'] = $this->dashboardModel->getDistribusiKondisi();

            // C. Jumlah Fasilitas Per Gedung (Untuk Chart.js Bar Chart)
            $data['fasilitas_per_gedung'] = $this->dashboardModel->getFasilitasPerGedung();

            // D. Laporan Kerusakan Terbaru
            $data['laporan_terbaru'] = $this->dashboardModel->getLaporanTerbaru(5);

            // E. Fasilitas yang Sering Rusak (Top Kerusakan)
            $data['fasilitas_sering_rusak'] = $this->dashboardModel->getFasilitasSeringRusak(5);

            // F. Fasilitas yang Perlu Perhatian (Kondisi Rusak / Ada Laporan Pending)
            $data['fasilitas_perlu_perhatian'] = $this->dashboardModel->getFasilitasPerluPerhatian(6);

            // G. Tugas Terjadwal (Khusus Petugas Sarpras)
            if ($role === 'petugas_sarpras') {
                $data['assigned_tasks'] = $this->dashboardModel->getAssignedTasks((int)$user['id'], 5);
                $this->renderDashboard('dashboard/petugas', $data);
            } else {
                $this->renderDashboard('dashboard/admin', $data);
            }

        // 2. DASHBOARD DOSEN & MAHASISWA (PENGGUNA)
        } else {
            $data['stats'] = $this->dashboardModel->getPenggunaStats((int)$user['id']);

            // Riwayat Laporan Pengguna Terkini
            $data['my_reports'] = $this->dashboardModel->getUserReports((int)$user['id'], 5);

            // Ruangan Tersedia
            $data['available_rooms'] = $this->dashboardModel->getAvailableRooms(6);

            // Pemeliharaan Publik Terbaru
            $data['public_maintenance'] = $this->dashboardModel->getPublicMaintenance(4);

            $this->renderDashboard('dashboard/pengguna', $data);
        }
    }

    private function renderDashboard($viewName, $data) {
        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view($viewName, $data);
        $this->view('layouts/dashboard_footer', $data);
    }
}
