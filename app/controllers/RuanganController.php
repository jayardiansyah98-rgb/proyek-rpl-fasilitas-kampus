<?php
/**
 * RuanganController
 * Modul Manajemen Master Data Ruangan & Laboratorium Kampus STMIK IKMI Cirebon
 * Mendukung RBAC (Admin, Petugas Sarpras, Pengguna Civitas), Filter, Pagination, Validasi Relasi
 */

class RuanganController extends Controller {
    protected $ruanganModel;
    protected $gedungModel;

    public function __construct() {
        // Halaman Ruangan dapat dilihat oleh seluruh pengguna terautentikasi
        Auth::requireLogin();
        $this->ruanganModel = $this->model('RuanganModel');
        $this->gedungModel = $this->model('GedungModel');
    }

    /**
     * Menampilkan daftar seluruh ruangan kampus dengan pencarian, filter gedung, dan pagination
     */
    public function index() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $gedungId = isset($_GET['gedung_id']) && $_GET['gedung_id'] !== '' ? (int)$_GET['gedung_id'] : null;
        $jenisRuangan = isset($_GET['jenis_ruangan']) && $_GET['jenis_ruangan'] !== '' ? trim($_GET['jenis_ruangan']) : null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;

        $totalRows = $this->ruanganModel->countAll($search, $gedungId, $jenisRuangan);
        $totalPages = ($totalRows > 0) ? (int)ceil($totalRows / $perPage) : 1;
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $ruanganList = $this->ruanganModel->getAll($search, $gedungId, $jenisRuangan, $page, $perPage);
        $gedungList = $this->gedungModel->getAllActive();

        $data = [
            'title' => 'Data Ruangan & Laboratorium - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'ruangan_list' => $ruanganList,
            'gedung_list' => $gedungList,
            'search' => $search,
            'gedung_id' => $gedungId,
            'jenis_ruangan' => $jenisRuangan,
            'page' => $page,
            'per_page' => $perPage,
            'total_rows' => $totalRows,
            'total_pages' => $totalPages
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('ruangan/index', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menampilkan informasi detail ruangan, inventaris fasilitas terpasang, dan riwayat laporan
     */
    public function detail($id = null) {
        if (!$id) {
            $_SESSION['flash_error'] = 'ID Ruangan tidak valid.';
            redirect('ruangan');
        }

        $ruangan = $this->ruanganModel->findById($id);
        if (!$ruangan) {
            $_SESSION['flash_error'] = 'Data ruangan tidak ditemukan.';
            redirect('ruangan');
        }

        $fasilitas = $this->ruanganModel->getFasilitasByRuangan($id);
        $laporan = $this->ruanganModel->getLaporanByRuangan($id);

        $data = [
            'title' => 'Detail ' . htmlspecialchars($ruangan['nama_ruangan']) . ' (' . htmlspecialchars($ruangan['kode_ruangan']) . ') - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'ruangan' => $ruangan,
            'fasilitas_list' => $fasilitas,
            'laporan_list' => $laporan
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('ruangan/detail', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menambah ruangan baru (Khusus Role Admin dan Petugas Sarpras)
     */
    public function tambah() {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        $errors = [];
        $old = [
            'kode_ruangan' => '',
            'nama_ruangan' => '',
            'gedung_id' => '',
            'lantai' => 1,
            'jenis_ruangan' => 'Kelas',
            'kapasitas' => 30,
            'status' => 'tersedia',
            'keterangan' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $errors['general'] = 'Sesi formulir telah kedaluwarsa atau token tidak valid (CSRF). Silakan muat ulang halaman.';
            } else {
                $old['kode_ruangan'] = strtoupper(trim($_POST['kode_ruangan'] ?? ''));
            $old['nama_ruangan'] = trim($_POST['nama_ruangan'] ?? '');
            $old['gedung_id'] = trim($_POST['gedung_id'] ?? '');
            $old['lantai'] = max(1, (int)($_POST['lantai'] ?? 1));
            $old['jenis_ruangan'] = trim($_POST['jenis_ruangan'] ?? 'Kelas');
            $old['kapasitas'] = max(0, (int)($_POST['kapasitas'] ?? 0));
            $old['status'] = trim($_POST['status'] ?? 'tersedia');
            $old['keterangan'] = trim($_POST['keterangan'] ?? '');

            // Validasi Kode Ruangan
            if (empty($old['kode_ruangan'])) {
                $errors['kode_ruangan'] = 'Kode ruangan wajib diisi.';
            } elseif (strlen($old['kode_ruangan']) > 30) {
                $errors['kode_ruangan'] = 'Kode ruangan maksimal 30 karakter.';
            } elseif ($this->ruanganModel->findByKode($old['kode_ruangan'])) {
                $errors['kode_ruangan'] = 'Kode ruangan "' . htmlspecialchars($old['kode_ruangan']) . '" sudah digunakan.';
            }

            // Validasi Nama Ruangan
            if (empty($old['nama_ruangan'])) {
                $errors['nama_ruangan'] = 'Nama ruangan wajib diisi.';
            } elseif (strlen($old['nama_ruangan']) > 100) {
                $errors['nama_ruangan'] = 'Nama ruangan maksimal 100 karakter.';
            }

            // Validasi Gedung
            if (empty($old['gedung_id'])) {
                $errors['gedung_id'] = 'Pilih lokasi gedung untuk ruangan ini.';
            } else {
                $gedungCheck = $this->gedungModel->findById($old['gedung_id']);
                if (!$gedungCheck) {
                    $errors['gedung_id'] = 'Gedung yang dipilih tidak valid.';
                }
            }

            // Validasi Jenis Ruangan
            $validJenis = ['Kelas', 'Laboratorium', 'Ruang Dosen', 'Ruang Administrasi', 'Ruang Rapat', 'Ruang Seminar', 'Lainnya'];
            if (!in_array($old['jenis_ruangan'], $validJenis)) {
                $errors['jenis_ruangan'] = 'Pilihan jenis ruangan tidak valid.';
            }

            // Validasi Status
            $validStatus = ['tersedia', 'digunakan', 'perbaikan', 'nonaktif'];
            if (!in_array($old['status'], $validStatus)) {
                $errors['status'] = 'Pilihan status ruangan tidak valid.';
            }

            if (empty($errors)) {
                try {
                    $insertId = $this->ruanganModel->create($old);
                    $_SESSION['flash_success'] = 'Ruangan ' . htmlspecialchars($old['nama_ruangan']) . ' (' . htmlspecialchars($old['kode_ruangan']) . ') berhasil ditambahkan.';
                    redirect('ruangan');
                } catch (Exception $e) {
                    $errors['general'] = 'Gagal menyimpan data ruangan: ' . $e->getMessage();
                }
            }
            }
        }

        $gedungList = $this->gedungModel->getAllActive();

        $data = [
            'title' => 'Tambah Ruangan Baru - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'gedung_list' => $gedungList,
            'errors' => $errors,
            'old' => $old
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('ruangan/create', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Mengubah data ruangan (Khusus Role Admin dan Petugas Sarpras)
     */
    public function edit($id = null) {
        Auth::requireRole(['admin', 'petugas_sarpras']);

        if (!$id) {
            $_SESSION['flash_error'] = 'ID Ruangan tidak valid.';
            redirect('ruangan');
        }

        $ruangan = $this->ruanganModel->findById($id);
        if (!$ruangan) {
            $_SESSION['flash_error'] = 'Data ruangan tidak ditemukan.';
            redirect('ruangan');
        }

        $errors = [];
        $old = [
            'kode_ruangan' => $ruangan['kode_ruangan'],
            'nama_ruangan' => $ruangan['nama_ruangan'],
            'gedung_id' => $ruangan['gedung_id'],
            'lantai' => $ruangan['lantai'],
            'jenis_ruangan' => $ruangan['jenis_ruangan'],
            'kapasitas' => $ruangan['kapasitas'],
            'status' => $ruangan['status'],
            'keterangan' => $ruangan['keterangan'] ?? ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $errors['general'] = 'Sesi formulir telah kedaluwarsa atau token tidak valid (CSRF). Silakan muat ulang halaman.';
            } else {
                $old['kode_ruangan'] = strtoupper(trim($_POST['kode_ruangan'] ?? ''));
            $old['nama_ruangan'] = trim($_POST['nama_ruangan'] ?? '');
            $old['gedung_id'] = trim($_POST['gedung_id'] ?? '');
            $old['lantai'] = max(1, (int)($_POST['lantai'] ?? 1));
            $old['jenis_ruangan'] = trim($_POST['jenis_ruangan'] ?? 'Kelas');
            $old['kapasitas'] = max(0, (int)($_POST['kapasitas'] ?? 0));
            $old['status'] = trim($_POST['status'] ?? 'tersedia');
            $old['keterangan'] = trim($_POST['keterangan'] ?? '');

            // Validasi Kode Ruangan
            if (empty($old['kode_ruangan'])) {
                $errors['kode_ruangan'] = 'Kode ruangan wajib diisi.';
            } elseif (strlen($old['kode_ruangan']) > 30) {
                $errors['kode_ruangan'] = 'Kode ruangan maksimal 30 karakter.';
            } elseif ($this->ruanganModel->findByKode($old['kode_ruangan'], $id)) {
                $errors['kode_ruangan'] = 'Kode ruangan "' . htmlspecialchars($old['kode_ruangan']) . '" sudah digunakan oleh ruangan lain.';
            }

            // Validasi Nama Ruangan
            if (empty($old['nama_ruangan'])) {
                $errors['nama_ruangan'] = 'Nama ruangan wajib diisi.';
            } elseif (strlen($old['nama_ruangan']) > 100) {
                $errors['nama_ruangan'] = 'Nama ruangan maksimal 100 karakter.';
            }

            // Validasi Gedung
            if (empty($old['gedung_id'])) {
                $errors['gedung_id'] = 'Pilih lokasi gedung untuk ruangan ini.';
            } else {
                $gedungCheck = $this->gedungModel->findById($old['gedung_id']);
                if (!$gedungCheck) {
                    $errors['gedung_id'] = 'Gedung yang dipilih tidak valid.';
                }
            }

            // Validasi Jenis Ruangan
            $validJenis = ['Kelas', 'Laboratorium', 'Ruang Dosen', 'Ruang Administrasi', 'Ruang Rapat', 'Ruang Seminar', 'Lainnya'];
            if (!in_array($old['jenis_ruangan'], $validJenis)) {
                $errors['jenis_ruangan'] = 'Pilihan jenis ruangan tidak valid.';
            }

            // Validasi Status
            $validStatus = ['tersedia', 'digunakan', 'perbaikan', 'nonaktif'];
            if (!in_array($old['status'], $validStatus)) {
                $errors['status'] = 'Pilihan status ruangan tidak valid.';
            }

            if (empty($errors)) {
                try {
                    $this->ruanganModel->update($id, $old);
                    $_SESSION['flash_success'] = 'Data ruangan ' . htmlspecialchars($old['nama_ruangan']) . ' berhasil diperbarui.';
                    redirect('ruangan');
                } catch (Exception $e) {
                    $errors['general'] = 'Gagal memperbarui data ruangan: ' . $e->getMessage();
                }
            }
            }
        }

        $gedungList = $this->gedungModel->getAllActive();

        $data = [
            'title' => 'Edit Ruangan ' . htmlspecialchars($ruangan['nama_ruangan']) . ' - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'ruangan' => $ruangan,
            'gedung_list' => $gedungList,
            'errors' => $errors,
            'old' => $old
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('ruangan/edit', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menghapus data ruangan dengan validasi integritas relasi (Khusus Role Admin)
     */
    public function hapus($id = null) {
        // Otorisasi: Hanya Admin yang dapat menghapus ruangan
        Auth::requireRole('admin');

        if (!csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan (CSRF) tidak valid.';
            redirect('ruangan');
            return;
        }

        if (!$id) {
            $_SESSION['flash_error'] = 'ID Ruangan tidak valid.';
            redirect('ruangan');
            return;
        }

        $ruangan = $this->ruanganModel->findById($id);
        if (!$ruangan) {
            $_SESSION['flash_error'] = 'Data ruangan tidak ditemukan.';
            redirect('ruangan');
        }

        // 1. Validasi Relasi Fasilitas
        $fasilitasCount = $this->ruanganModel->countFasilitas($id);
        if ($fasilitasCount > 0) {
            $_SESSION['flash_error'] = 'Ruangan "' . htmlspecialchars($ruangan['nama_ruangan']) . '" tidak dapat dihapus karena masih memiliki ' . $fasilitasCount . ' unit fasilitas/inventaris terpasang di dalamnya. Pindahkan atau hapus fasilitas ruangan terlebih dahulu.';
            redirect('ruangan');
        }

        // 2. Validasi Relasi Laporan Kerusakan
        $laporanCount = $this->ruanganModel->countLaporan($id);
        if ($laporanCount > 0) {
            $_SESSION['flash_error'] = 'Ruangan "' . htmlspecialchars($ruangan['nama_ruangan']) . '" tidak dapat dihapus karena tercatat dalam ' . $laporanCount . ' riwayat laporan kerusakan fasilitas kampus.';
            redirect('ruangan');
        }

        // Eksekusi Hapus Ruangan
        try {
            $this->ruanganModel->delete($id);
            $_SESSION['flash_success'] = 'Ruangan "' . htmlspecialchars($ruangan['nama_ruangan']) . '" (' . htmlspecialchars($ruangan['kode_ruangan']) . ') berhasil dihapus.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Gagal menghapus ruangan karena batasan relasi database: ' . $e->getMessage();
        }

        redirect('ruangan');
    }
}
