<?php
/**
 * GedungController
 * Modul Manajemen Master Gedung Kampus STMIK IKMI Cirebon
 * Mendukung CRUD, Search, Pagination, Relational Validation, dan RBAC
 */

class GedungController extends Controller {
    protected $gedungModel;

    public function __construct() {
        // Akses gedung terbuka untuk Admin dan Petugas Sarpras
        Auth::requireRole(['admin', 'petugas_sarpras']);
        $this->gedungModel = $this->model('GedungModel');
    }

    /**
     * Menampilkan daftar gedung dengan pencarian dan pagination
     */
    public function index() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 8;

        $totalRows = $this->gedungModel->countAll($search);
        $totalPages = ($totalRows > 0) ? (int)ceil($totalRows / $perPage) : 1;
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $gedungList = $this->gedungModel->getAll($search, $page, $perPage);

        $data = [
            'title' => 'Data Master Gedung - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'gedung_list' => $gedungList,
            'search' => $search,
            'page' => $page,
            'per_page' => $perPage,
            'total_rows' => $totalRows,
            'total_pages' => $totalPages
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('gedung/index', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menampilkan detail gedung beserta daftar ruangan di dalamnya
     */
    public function detail($id = null) {
        if (!$id) {
            $_SESSION['flash_error'] = 'ID Gedung tidak valid.';
            redirect('gedung');
        }

        $gedung = $this->gedungModel->findById($id);
        if (!$gedung) {
            $_SESSION['flash_error'] = 'Data gedung tidak ditemukan.';
            redirect('gedung');
        }

        $ruanganList = $this->gedungModel->getRuanganByGedung($id);

        $data = [
            'title' => 'Detail Gedung ' . htmlspecialchars($gedung['nama_gedung']) . ' - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'gedung' => $gedung,
            'ruangan_list' => $ruanganList
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('gedung/detail', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menambah data gedung baru (Khusus Role Admin)
     */
    public function tambah() {
        Auth::requireRole('admin');

        $errors = [];
        $old = [
            'kode_gedung' => '',
            'nama_gedung' => '',
            'lokasi' => '',
            'keterangan' => '',
            'jumlah_lantai' => 1,
            'status' => 'aktif'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $errors['general'] = 'Sesi formulir telah kedaluwarsa atau token tidak valid (CSRF). Silakan muat ulang halaman.';
            } else {
                $old['kode_gedung'] = strtoupper(trim($_POST['kode_gedung'] ?? ''));
            $old['nama_gedung'] = trim($_POST['nama_gedung'] ?? '');
            $old['lokasi'] = trim($_POST['lokasi'] ?? '');
            $old['keterangan'] = trim($_POST['keterangan'] ?? '');
            $old['jumlah_lantai'] = max(1, (int)($_POST['jumlah_lantai'] ?? 1));
            $old['status'] = in_array($_POST['status'] ?? '', ['aktif', 'renovasi', 'nonaktif']) ? $_POST['status'] : 'aktif';

            // Validasi Kode Gedung
            if (empty($old['kode_gedung'])) {
                $errors['kode_gedung'] = 'Kode gedung wajib diisi.';
            } elseif (strlen($old['kode_gedung']) > 20) {
                $errors['kode_gedung'] = 'Kode gedung maksimal 20 karakter.';
            } elseif ($this->gedungModel->findByKode($old['kode_gedung'])) {
                $errors['kode_gedung'] = 'Kode gedung "' . htmlspecialchars($old['kode_gedung']) . '" sudah digunakan.';
            }

            // Validasi Nama Gedung
            if (empty($old['nama_gedung'])) {
                $errors['nama_gedung'] = 'Nama gedung wajib diisi.';
            } elseif (strlen($old['nama_gedung']) > 100) {
                $errors['nama_gedung'] = 'Nama gedung maksimal 100 karakter.';
            }

            // Validasi Lokasi
            if (empty($old['lokasi'])) {
                $errors['lokasi'] = 'Lokasi gedung wajib diisi.';
            } elseif (strlen($old['lokasi']) > 255) {
                $errors['lokasi'] = 'Lokasi gedung maksimal 255 karakter.';
            }

            if (empty($errors)) {
                try {
                    $insertId = $this->gedungModel->create($old);
                    $_SESSION['flash_success'] = 'Gedung ' . htmlspecialchars($old['nama_gedung']) . ' berhasil ditambahkan.';
                    redirect('gedung');
                } catch (Exception $e) {
                    $errors['general'] = 'Gagal menyimpan data gedung: ' . $e->getMessage();
                }
            }
            }
        }

        $data = [
            'title' => 'Tambah Gedung Baru - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'errors' => $errors,
            'old' => $old
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('gedung/create', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Mengubah data gedung (Khusus Role Admin)
     */
    public function edit($id = null) {
        Auth::requireRole('admin');

        if (!$id) {
            $_SESSION['flash_error'] = 'ID Gedung tidak valid.';
            redirect('gedung');
        }

        $gedung = $this->gedungModel->findById($id);
        if (!$gedung) {
            $_SESSION['flash_error'] = 'Data gedung tidak ditemukan.';
            redirect('gedung');
        }

        $errors = [];
        $old = [
            'kode_gedung' => $gedung['kode_gedung'],
            'nama_gedung' => $gedung['nama_gedung'],
            'lokasi' => $gedung['lokasi'] ?? '',
            'keterangan' => $gedung['keterangan'] ?? $gedung['deskripsi'] ?? '',
            'jumlah_lantai' => $gedung['jumlah_lantai'] ?? 1,
            'status' => $gedung['status'] ?? 'aktif'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $errors['general'] = 'Sesi formulir telah kedaluwarsa atau token tidak valid (CSRF). Silakan muat ulang halaman.';
            } else {
                $old['kode_gedung'] = strtoupper(trim($_POST['kode_gedung'] ?? ''));
            $old['nama_gedung'] = trim($_POST['nama_gedung'] ?? '');
            $old['lokasi'] = trim($_POST['lokasi'] ?? '');
            $old['keterangan'] = trim($_POST['keterangan'] ?? '');
            $old['jumlah_lantai'] = max(1, (int)($_POST['jumlah_lantai'] ?? 1));
            $old['status'] = in_array($_POST['status'] ?? '', ['aktif', 'renovasi', 'nonaktif']) ? $_POST['status'] : 'aktif';

            // Validasi Kode Gedung
            if (empty($old['kode_gedung'])) {
                $errors['kode_gedung'] = 'Kode gedung wajib diisi.';
            } elseif (strlen($old['kode_gedung']) > 20) {
                $errors['kode_gedung'] = 'Kode gedung maksimal 20 karakter.';
            } elseif ($this->gedungModel->findByKode($old['kode_gedung'], $id)) {
                $errors['kode_gedung'] = 'Kode gedung "' . htmlspecialchars($old['kode_gedung']) . '" sudah digunakan oleh gedung lain.';
            }

            // Validasi Nama Gedung
            if (empty($old['nama_gedung'])) {
                $errors['nama_gedung'] = 'Nama gedung wajib diisi.';
            } elseif (strlen($old['nama_gedung']) > 100) {
                $errors['nama_gedung'] = 'Nama gedung maksimal 100 karakter.';
            }

            // Validasi Lokasi
            if (empty($old['lokasi'])) {
                $errors['lokasi'] = 'Lokasi gedung wajib diisi.';
            } elseif (strlen($old['lokasi']) > 255) {
                $errors['lokasi'] = 'Lokasi gedung maksimal 255 karakter.';
            }

            if (empty($errors)) {
                try {
                    $this->gedungModel->update($id, $old);
                    $_SESSION['flash_success'] = 'Data gedung ' . htmlspecialchars($old['nama_gedung']) . ' berhasil diperbarui.';
                    redirect('gedung');
                } catch (Exception $e) {
                    $errors['general'] = 'Gagal memperbarui data gedung: ' . $e->getMessage();
                }
            }
            }
        }

        $data = [
            'title' => 'Edit Gedung ' . htmlspecialchars($gedung['nama_gedung']) . ' - ' . APP_NAME,
            'user' => Auth::user(),
            'role' => Auth::role(),
            'gedung' => $gedung,
            'errors' => $errors,
            'old' => $old
        ];

        $this->view('layouts/dashboard_header', $data);
        $this->view('layouts/sidebar', $data);
        $this->view('layouts/topbar', $data);
        $this->view('gedung/edit', $data);
        $this->view('layouts/dashboard_footer', $data);
    }

    /**
     * Menghapus gedung dengan validasi integritas relasi (Khusus Role Admin)
     */
    public function hapus($id = null) {
        Auth::requireRole('admin');

        if (!csrf_validate()) {
            $_SESSION['flash_error'] = 'Token keamanan (CSRF) tidak valid.';
            redirect('gedung');
            return;
        }

        if (!$id) {
            $_SESSION['flash_error'] = 'ID Gedung tidak valid.';
            redirect('gedung');
            return;
        }

        $gedung = $this->gedungModel->findById($id);
        if (!$gedung) {
            $_SESSION['flash_error'] = 'Data gedung tidak ditemukan.';
            redirect('gedung');
        }

        // Validasi relasi database: pastikan tidak ada ruangan yang terhubung
        $ruanganCount = $this->gedungModel->countRuangan($id);
        if ($ruanganCount > 0) {
            $_SESSION['flash_error'] = 'Gedung "' . htmlspecialchars($gedung['nama_gedung']) . '" tidak dapat dihapus karena masih memiliki ' . $ruanganCount . ' ruangan yang terdaftar di dalamnya. Hapus atau pindahkan data ruangan terlebih dahulu.';
            redirect('gedung');
        }

        try {
            $this->gedungModel->delete($id);
            $_SESSION['flash_success'] = 'Gedung "' . htmlspecialchars($gedung['nama_gedung']) . '" berhasil dihapus.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Gagal menghapus gedung karena terjadi pembatasan integritas data: ' . $e->getMessage();
        }

        redirect('gedung');
    }
}
