<?php
/**
 * ReportController
 * Modul Rekapitulasi & Pelaporan Eksekutif Sarana Prasarana STMIK IKMI Cirebon
 * Mendukung filter multi-dimensi, cetak dokumen resmi (print preview), dan export CSV
 */

class ReportController extends Controller {
    protected $reportModel;
    protected $gedungModel;
    protected $ruanganModel;

    public function __construct() {
        // Proteksi: Admin dan Petugas Sarpras
        Auth::requireRole(['admin', 'petugas_sarpras']);
        $this->reportModel = $this->model('ReportModel');
        $this->gedungModel = $this->model('GedungModel');
        $this->ruanganModel = $this->model('RuanganModel');
    }

    /**
     * Halaman Utama Rekapitulasi & Reporting
     */
    public function index() {
        $type = isset($_GET['type']) ? trim($_GET['type']) : 'fasilitas';
        $validTypes = ['fasilitas', 'gedung', 'ruangan', 'kondisi', 'kerusakan', 'pemeliharaan'];
        if (!in_array($type, $validTypes, true)) {
            $type = 'fasilitas';
        }

        $filters = [
            'gedung_id' => isset($_GET['gedung_id']) && $_GET['gedung_id'] !== '' ? (int)$_GET['gedung_id'] : null,
            'ruangan_id' => isset($_GET['ruangan_id']) && $_GET['ruangan_id'] !== '' ? (int)$_GET['ruangan_id'] : null,
            'kondisi' => isset($_GET['kondisi']) && $_GET['kondisi'] !== '' ? trim($_GET['kondisi']) : null,
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : null,
            'prioritas' => isset($_GET['prioritas']) && $_GET['prioritas'] !== '' ? trim($_GET['prioritas']) : null,
            'jenis_tindakan' => isset($_GET['jenis_tindakan']) && $_GET['jenis_tindakan'] !== '' ? trim($_GET['jenis_tindakan']) : null,
            'tanggal_dari' => isset($_GET['tanggal_dari']) && $_GET['tanggal_dari'] !== '' ? trim($_GET['tanggal_dari']) : null,
            'tanggal_sampai' => isset($_GET['tanggal_sampai']) && $_GET['tanggal_sampai'] !== '' ? trim($_GET['tanggal_sampai']) : null,
            'search' => isset($_GET['search']) ? trim($_GET['search']) : ''
        ];

        $reportData = $this->fetchReportData($type, $filters);

        $gedungList = $this->gedungModel->getAllActive();
        $ruanganList = $this->ruanganModel->getAll();

        $data = [
            'title' => 'Laporan & Rekapitulasi Sarpras - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'type' => $type,
            'filters' => $filters,
            'report_data' => $reportData,
            'gedung_list' => $gedungList,
            'ruangan_list' => $ruanganList,
            'report_title' => $this->getReportTitle($type)
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('report/index', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Cetak Dokumen Rekapitulasi (Print-ready layout dengan KOP Surat STMIK IKMI)
     */
    public function cetak() {
        $type = isset($_GET['type']) ? trim($_GET['type']) : 'fasilitas';
        $filters = [
            'gedung_id' => isset($_GET['gedung_id']) && $_GET['gedung_id'] !== '' ? (int)$_GET['gedung_id'] : null,
            'ruangan_id' => isset($_GET['ruangan_id']) && $_GET['ruangan_id'] !== '' ? (int)$_GET['ruangan_id'] : null,
            'kondisi' => isset($_GET['kondisi']) && $_GET['kondisi'] !== '' ? trim($_GET['kondisi']) : null,
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : null,
            'prioritas' => isset($_GET['prioritas']) && $_GET['prioritas'] !== '' ? trim($_GET['prioritas']) : null,
            'jenis_tindakan' => isset($_GET['jenis_tindakan']) && $_GET['jenis_tindakan'] !== '' ? trim($_GET['jenis_tindakan']) : null,
            'tanggal_dari' => isset($_GET['tanggal_dari']) && $_GET['tanggal_dari'] !== '' ? trim($_GET['tanggal_dari']) : null,
            'tanggal_sampai' => isset($_GET['tanggal_sampai']) && $_GET['tanggal_sampai'] !== '' ? trim($_GET['tanggal_sampai']) : null,
            'search' => isset($_GET['search']) ? trim($_GET['search']) : ''
        ];

        $reportData = $this->fetchReportData($type, $filters);

        $selectedGedungName = null;
        if (!empty($filters['gedung_id'])) {
            $g = $this->gedungModel->findById($filters['gedung_id']);
            $selectedGedungName = $g ? $g['nama_gedung'] : null;
        }

        $selectedRuanganName = null;
        if (!empty($filters['ruangan_id'])) {
            $r = $this->ruanganModel->findById($filters['ruangan_id']);
            $selectedRuanganName = $r ? $r['nama_ruangan'] : null;
        }

        $data = [
            'title' => 'Cetak ' . $this->getReportTitle($type) . ' - ' . APP_NAME,
            'user' => Auth::user(),
            'type' => $type,
            'report_data' => $reportData,
            'filters' => $filters,
            'selected_gedung_name' => $selectedGedungName,
            'selected_ruangan_name' => $selectedRuanganName,
            'report_title' => $this->getReportTitle($type)
        ];

        // Tampilkan halaman cetak tanpa sidebar/topbar
        $this->view('report/print', $data);
    }

    /**
     * Export Dokumen ke Format CSV (Excel Compatible dengan UTF-8 BOM)
     */
    public function exportCsv() {
        $type = isset($_GET['type']) ? trim($_GET['type']) : 'fasilitas';
        $filters = [
            'gedung_id' => isset($_GET['gedung_id']) && $_GET['gedung_id'] !== '' ? (int)$_GET['gedung_id'] : null,
            'ruangan_id' => isset($_GET['ruangan_id']) && $_GET['ruangan_id'] !== '' ? (int)$_GET['ruangan_id'] : null,
            'kondisi' => isset($_GET['kondisi']) && $_GET['kondisi'] !== '' ? trim($_GET['kondisi']) : null,
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : null,
            'prioritas' => isset($_GET['prioritas']) && $_GET['prioritas'] !== '' ? trim($_GET['prioritas']) : null,
            'jenis_tindakan' => isset($_GET['jenis_tindakan']) && $_GET['jenis_tindakan'] !== '' ? trim($_GET['jenis_tindakan']) : null,
            'tanggal_dari' => isset($_GET['tanggal_dari']) && $_GET['tanggal_dari'] !== '' ? trim($_GET['tanggal_dari']) : null,
            'tanggal_sampai' => isset($_GET['tanggal_sampai']) && $_GET['tanggal_sampai'] !== '' ? trim($_GET['tanggal_sampai']) : null,
            'search' => isset($_GET['search']) ? trim($_GET['search']) : ''
        ];

        $reportData = $this->fetchReportData($type, $filters);
        $filename = 'laporan_' . $type . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Tulis UTF-8 BOM agar Excel menampilkan aksen dan karakter dengan benar
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        switch ($type) {
            case 'gedung':
                fputcsv($output, ['No', 'Kode Gedung', 'Nama Gedung', 'Lokasi', 'Lantai', 'Total Ruangan', 'Jenis Fasilitas', 'Total Unit', 'Kondisi Baik', 'Rusak Ringan', 'Rusak Berat', 'Tidak Layak', 'Laporan Kerusakan']);
                $no = 1;
                foreach ($reportData as $row) {
                    fputcsv($output, [
                        $no++,
                        $row['kode_gedung'],
                        $row['nama_gedung'],
                        $row['lokasi'],
                        $row['jumlah_lantai'],
                        $row['total_ruangan'],
                        $row['total_jenis_fasilitas'],
                        $row['total_unit_fasilitas'],
                        $row['fasilitas_baik'],
                        $row['fasilitas_rusak_ringan'],
                        $row['fasilitas_rusak_berat'],
                        $row['fasilitas_tidak_layak'],
                        $row['total_tiket_laporan']
                    ]);
                }
                break;

            case 'ruangan':
                fputcsv($output, ['No', 'Kode Ruangan', 'Nama Ruangan', 'Gedung', 'Lantai', 'Jenis', 'Kapasitas', 'Status', 'Total Item', 'Total Unit', 'Item Baik', 'Item Rusak', 'Total Laporan']);
                $no = 1;
                foreach ($reportData as $row) {
                    fputcsv($output, [
                        $no++,
                        $row['kode_ruangan'],
                        $row['nama_ruangan'],
                        $row['nama_gedung'],
                        $row['lantai'],
                        $row['jenis_ruangan'],
                        $row['kapasitas'],
                        $row['status_ruangan'],
                        $row['total_jenis_fasilitas'],
                        $row['total_unit_fasilitas'],
                        $row['item_baik'],
                        $row['item_rusak'],
                        $row['total_laporan']
                    ]);
                }
                break;

            case 'kondisi':
                fputcsv($output, ['No', 'Kode Inventaris', 'Nama Fasilitas', 'Kategori', 'Kondisi', 'Jumlah', 'Tahun', 'Ruangan', 'Gedung', 'Tiket Aktif', 'Total Pemeliharaan']);
                $no = 1;
                foreach ($reportData as $row) {
                    fputcsv($output, [
                        $no++,
                        $row['kode_inventaris'],
                        $row['nama_fasilitas'],
                        $row['nama_kategori'],
                        ucfirst(str_replace('_', ' ', $row['kondisi'])),
                        $row['jumlah'],
                        $row['tahun_pengadaan'],
                        $row['nama_ruangan'],
                        $row['nama_gedung'],
                        $row['active_laporan'],
                        $row['total_pemeliharaan']
                    ]);
                }
                break;

            case 'kerusakan':
                fputcsv($output, ['No', 'Kode Laporan', 'Tanggal', 'Nama Pelapor', 'NIM/NIP', 'Jenis Pengguna', 'Fasilitas', 'Kode Inventaris', 'Ruangan', 'Gedung', 'Prioritas', 'Status', 'Deskripsi Keluhan', 'Catatan Petugas']);
                $no = 1;
                foreach ($reportData as $row) {
                    fputcsv($output, [
                        $no++,
                        $row['kode_laporan'],
                        date('d/m/Y H:i', strtotime($row['created_at'])),
                        $row['nama_pelapor'],
                        $row['nim_nip_pelapor'],
                        $row['jenis_pengguna'],
                        $row['nama_fasilitas'],
                        $row['kode_inventaris'],
                        $row['nama_ruangan'],
                        $row['nama_gedung'],
                        ucfirst($row['prioritas']),
                        ucfirst($row['status']),
                        $row['deskripsi'],
                        $row['catatan_petugas'] ?? ''
                    ]);
                }
                break;

            case 'pemeliharaan':
                fputcsv($output, ['No', 'Kode Pemeliharaan', 'Tanggal', 'Fasilitas', 'Kode Inventaris', 'Ruangan', 'Gedung', 'Teknisi', 'Jenis Tindakan', 'Kondisi Sebelum', 'Kondisi Sesudah', 'Biaya (Rp)', 'Status', 'Deskripsi', 'Keterangan']);
                $no = 1;
                foreach ($reportData as $row) {
                    $tgl = !empty($row['tanggal']) ? $row['tanggal'] : $row['tanggal_mulai'];
                    fputcsv($output, [
                        $no++,
                        $row['kode_pemeliharaan'],
                        date('d/m/Y', strtotime($tgl)),
                        $row['nama_fasilitas'],
                        $row['kode_inventaris'],
                        $row['nama_ruangan'],
                        $row['nama_gedung'],
                        $row['nama_petugas'],
                        $row['jenis_tindakan'],
                        ucfirst(str_replace('_', ' ', $row['kondisi_sebelum'] ?? 'baik')),
                        ucfirst(str_replace('_', ' ', $row['kondisi_sesudah'] ?? 'baik')),
                        $row['biaya'],
                        ucfirst(str_replace('_', ' ', $row['status'])),
                        $row['deskripsi'] ?? '',
                        $row['keterangan'] ?? ''
                    ]);
                }
                break;

            case 'fasilitas':
            default:
                fputcsv($output, ['No', 'Kode Inventaris', 'Nama Fasilitas', 'Kategori', 'Gedung', 'Ruangan', 'Jumlah', 'Kondisi', 'Tahun', 'Total Tiket Kerusakan', 'Total Pemeliharaan', 'Akumulasi Biaya (Rp)']);
                $no = 1;
                foreach ($reportData as $row) {
                    fputcsv($output, [
                        $no++,
                        $row['kode_inventaris'],
                        $row['nama_fasilitas'],
                        $row['nama_kategori'],
                        $row['nama_gedung'],
                        $row['nama_ruangan'],
                        $row['jumlah'],
                        ucfirst(str_replace('_', ' ', $row['kondisi'])),
                        $row['tahun_pengadaan'],
                        $row['total_laporan'],
                        $row['total_pemeliharaan'],
                        $row['total_biaya_pemeliharaan']
                    ]);
                }
                break;
        }

        fclose($output);
        exit;
    }

    /**
     * Helper mengambil dataset sesuai kategori laporan
     */
    private function fetchReportData($type, array $filters) {
        switch ($type) {
            case 'gedung':
                return $this->reportModel->getLaporanPerGedung($filters);
            case 'ruangan':
                return $this->reportModel->getLaporanPerRuangan($filters);
            case 'kondisi':
                return $this->reportModel->getLaporanKondisi($filters);
            case 'kerusakan':
                return $this->reportModel->getLaporanKerusakan($filters);
            case 'pemeliharaan':
                return $this->reportModel->getLaporanPemeliharaan($filters);
            case 'fasilitas':
            default:
                return $this->reportModel->getLaporanFasilitas($filters);
        }
    }

    /**
     * Helper mendapatkan judul laporan
     */
    private function getReportTitle($type) {
        switch ($type) {
            case 'gedung':
                return 'Laporan Rekapitulasi Fasilitas per Gedung';
            case 'ruangan':
                return 'Laporan Rekapitulasi Fasilitas per Ruangan';
            case 'kondisi':
                return 'Laporan Fasilitas Berdasarkan Kondisi Kelaikan Fisik';
            case 'kerusakan':
                return 'Laporan Riwayat Tiket Pengaduan Kerusakan';
            case 'pemeliharaan':
                return 'Laporan Rekapitulasi Tindakan Pemeliharaan & Servis';
            case 'fasilitas':
            default:
                return 'Laporan Master Data Seluruh Fasilitas Kampus';
        }
    }
}
